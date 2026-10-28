<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang.
     */
    public function index()
    {
        $cartItems = session('cart', []);

        $items = [];
        $subtotal = 0;
        $discount = 0;

        if (!empty($cartItems)) {

            foreach ($cartItems as $itemId => $quantity) {

                $item = Item::with('category')->find($itemId);

                if (!$item) {
                    continue;
                }

                $quantity = (int) $quantity;

                $itemTotal = (float) $item->price * $quantity;

                $itemDiscount =
                    $itemTotal * ((float) $item->discount / 100);

                $items[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->price,
                    'quantity' => $quantity,
                    'unit' => $item->unit,
                    'discount' => (float) $item->discount,
                    'total' => (int) round($itemTotal),
                    'discounted_total' =>
                        $itemTotal - $itemDiscount,
                ];

                $subtotal += $itemTotal;
                $discount += $itemDiscount;
            }
        }

        // Ongkir sementara gratis
        $shipping = 0;

        $total = $subtotal - $discount + $shipping;

        return Inertia::render('clients/Cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
        ]);
    }


    /**
     * Menambahkan produk ke keranjang.
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $cart = session('cart', []);

        $itemId = (int) $validated['item_id'];
        $quantity = (int) $validated['quantity'];

        if (isset($cart[$itemId])) {
            $cart[$itemId] += $quantity;
        } else {
            $cart[$itemId] = $quantity;
        }

        session([
            'cart' => $cart,
        ]);

        return back()->with(
            'success',
            'Item added to cart successfully!'
        );
    }


    /**
     * Mengubah jumlah produk di keranjang.
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $cart = session('cart', []);

        $itemId = (int) $validated['item_id'];
        $quantity = (int) $validated['quantity'];

        if ($quantity > 0) {
            $cart[$itemId] = $quantity;
        } else {
            unset($cart[$itemId]);
        }

        session([
            'cart' => $cart,
        ]);

        return back()->with(
            'success',
            'Cart updated successfully!'
        );
    }


    /**
     * Menghapus produk dari keranjang.
     */
    public function removeFromCart(Request $request)
    {
        $validated = $request->validate([
            'item_id' => [
                'required',
                'integer',
                'exists:items,id',
            ],
        ]);

        $cart = session('cart', []);

        unset($cart[(int) $validated['item_id']]);

        session([
            'cart' => $cart,
        ]);

        return back()->with(
            'success',
            'Item removed from cart successfully!'
        );
    }


    /**
     * Mengambil jumlah item dan total harga keranjang.
     */
    public function getCartCount()
    {
        $cart = session('cart', []);

        $count = 0;
        $total = 0;

        foreach ($cart as $itemId => $quantity) {

            $item = Item::find($itemId);

            if (!$item) {
                continue;
            }

            $quantity = (int) $quantity;

            $count += $quantity;

            $itemTotal =
                (float) $item->price * $quantity;

            $itemDiscount =
                $itemTotal *
                ((float) $item->discount / 100);

            $total +=
                $itemTotal - $itemDiscount;
        }

        return response()->json([
            'count' => $count,
            'total' => $total,
        ]);
    }


    /**
     * Checkout keranjang.
     */
    public function checkout(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI REQUEST
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'payment_method' => [
                'required',
                'string',
                'in:cash,bank,e-wallet',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | CEK USER
        |--------------------------------------------------------------------------
        */

        if (!Auth::check()) {

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Silakan login terlebih dahulu.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL CART
        |--------------------------------------------------------------------------
        */

        $cart = session('cart', []);

        if (empty($cart)) {

            return back()->with(
                'error',
                'Keranjang masih kosong.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PROSES TRANSAKSI
        |--------------------------------------------------------------------------
        */

        try {

            $transaction = DB::transaction(function () use (
                $cart,
                $validated
            ) {

                $subtotal = 0;
                $discount = 0;

                $transactionItems = [];


                /*
                |--------------------------------------------------------------------------
                | CEK SEMUA PRODUK DAN STOK
                |--------------------------------------------------------------------------
                */

                foreach ($cart as $itemId => $quantity) {

                    $quantity = (int) $quantity;

                    if ($quantity < 1) {
                        throw new \Exception(
                            'Jumlah produk tidak valid.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | LOCK PRODUK
                    |--------------------------------------------------------------------------
                    |
                    | Menghindari stok berubah bersamaan saat checkout.
                    |
                    */

                    $item = Item::where(
                        'id',
                        $itemId
                    )
                        ->lockForUpdate()
                        ->first();


                    if (!$item) {

                        throw new \Exception(
                            "Produk dengan ID {$itemId} tidak ditemukan."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CEK STOK
                    |--------------------------------------------------------------------------
                    */

                    $stock = (int) $item->stock;

                    if ($stock < $quantity) {

                        throw new \Exception(
                            "Stok {$item->name} tidak mencukupi. " .
                            "Stok tersedia: {$stock}, " .
                            "jumlah yang dipesan: {$quantity}."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG HARGA
                    |--------------------------------------------------------------------------
                    */

                    $price = (float) $item->price;

                    $itemTotal =
                        $price * $quantity;

                    $itemDiscount =
                        $itemTotal *
                        ((float) $item->discount / 100);


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DATA ITEM UNTUK TRANSAKSI
                    |--------------------------------------------------------------------------
                    */

                    $transactionItems[] = [
                        'item' => $item,
                        'quantity' => $quantity,
                        'price_at_time' => $price,
                    ];


                    $subtotal += $itemTotal;
                    $discount += $itemDiscount;
                }


                /*
                |--------------------------------------------------------------------------
                | HITUNG TOTAL
                |--------------------------------------------------------------------------
                */

                $shipping = 0;

                $total =
                    $subtotal -
                    $discount +
                    $shipping;


                /*
                |--------------------------------------------------------------------------
                | BUAT TRANSAKSI
                |--------------------------------------------------------------------------
                */

                $transaction = Transaction::create([
                    'client_id' => Auth::id(),
                    'total' => $total,
                    'note' => $validated['note'] ?? null,
                    'payment_method' =>
                        $validated['payment_method'],
                    'status' => 'pending',
                ]);


                /*
                |--------------------------------------------------------------------------
                | BUAT DETAIL TRANSAKSI
                |--------------------------------------------------------------------------
                */

                foreach ($transactionItems as $transactionItem) {

                    $item =
                        $transactionItem['item'];

                    $quantity =
                        $transactionItem['quantity'];

                    $price =
                        $transactionItem['price_at_time'];


                    TransactionDetail::create([
                        'transaction_id' =>
                            $transaction->id,

                        'item_id' =>
                            $item->id,

                        'quantity' =>
                            $quantity,

                        'price_at_time' =>
                            $price,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | KURANGI STOK
                    |--------------------------------------------------------------------------
                    */

                    $item->decrement(
                        'stock',
                        $quantity
                    );
                }


                return $transaction;
            });


            /*
            |--------------------------------------------------------------------------
            | KOSONGKAN CART
            |--------------------------------------------------------------------------
            */

            session()->forget('cart');


            /*
            |--------------------------------------------------------------------------
            | REDIRECT KE ORDER CONFIRMATION
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route(
                    'client.orders.show',
                    [
                        'transaction' =>
                            $transaction->id,
                    ]
                )
                ->with(
                    'success',
                    'Pesanan berhasil dibuat.'
                );


        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | CATAT ERROR KE LARAVEL LOG
            |--------------------------------------------------------------------------
            */

            Log::error(
                'CHECKOUT ERROR',
                [
                    'user_id' => Auth::id(),
                    'payment_method' =>
                        $request->payment_method,
                    'cart' => $cart,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | KEMBALI KE CART DENGAN ERROR
            |--------------------------------------------------------------------------
            */

            return back()->withErrors([
                'checkout' =>
                    'Checkout gagal: ' .
                    $e->getMessage(),
            ]);
        }
    }
}