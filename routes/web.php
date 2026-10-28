
<?php

use App\Http\Controllers\Admin\AdminAddressController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminItemController;
use App\Http\Controllers\Admin\AdminRatingController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminTransactionDetailController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Client\CartController;
use App\Http\Controllers\Client\ProfileControllerClient;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\MidtransNotificationController;
use App\Services\MidtransService;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
})->name('landing.page');

/*
|--------------------------------------------------------------------------
| HALAMAN UMUM
|--------------------------------------------------------------------------
*/

Route::get('/offers', function () {
    return Inertia::render('offers');
});

Route::get('/order', function () {
    return Inertia::render('order');
});

Route::get('/Homepage', function () {
    $items = Item::with('category')
        ->where('is_available', true)
        ->where('stock', '>', 0)
        ->get();

    return Inertia::render('Homepage', [
        'items' => $items,
    ]);
})->name('Homepage');

Route::get('/Delivery', function () {
    return Inertia::render('Delivery');
});

/*
|--------------------------------------------------------------------------
| MENU / PRODUK
|--------------------------------------------------------------------------
*/

Route::get('/menu', [
    CategoryController::class,
    'index',
]);

Route::get('/menu/{slug}', [
    CategoryController::class,
    'show',
]);

/*
|--------------------------------------------------------------------------
| PESANAN SAYA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/pesanan-saya', function (MidtransService $midtrans) {
        Transaction::where('client_id', Auth::id())
            ->where('status', 'pending')
            ->whereIn('payment_method', ['bank', 'e-wallet'])
            ->whereNotNull('midtrans_order_id')
            ->get()
            ->each(fn(Transaction $transaction) => $midtrans->refreshStatus($transaction));

        $transactions = Transaction::with(['details.item'])
            ->where('client_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('PesananSaya', [
            'transactions' => $transactions,
            'user' => Auth::user(),
        ]);
    })->name('pesanan.saya');
});

/*
|--------------------------------------------------------------------------
| WEBHOOK MIDTRANS (tanpa auth, dipanggil server Midtrans)
|--------------------------------------------------------------------------
*/

Route::post('/midtrans/notification', [
    MidtransNotificationController::class,
    'handle',
]);


/*
|--------------------------------------------------------------------------
| USER YANG SUDAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER HOME
    |--------------------------------------------------------------------------
    */

    Route::prefix('user')->group(function () {
        Route::get('/', function () {
            return Inertia::render('clients/welcome');
        })->name('home');
    });

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileControllerClient::class,
        'show',
    ])->name('profile.show');

    Route::post('/profile', [
        ProfileControllerClient::class,
        'update',
    ])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | CLIENT / CART / CHECKOUT
    |--------------------------------------------------------------------------
    */

    Route::prefix('client')
        ->name('client.')
        ->group(function () {

            // CART
            Route::get('/cart', [
                CartController::class,
                'index',
            ])->name('cart.index');

            // TAMBAH KE CART
            Route::post('/cart/add', [
                CartController::class,
                'addToCart',
            ])->name('cart.add');

            // UPDATE CART
            Route::patch('/cart/update', [
                CartController::class,
                'updateCart',
            ])->name('cart.update');

            // HAPUS CART
            Route::delete('/cart/remove', [
                CartController::class,
                'removeFromCart',
            ])->name('cart.remove');

            // CHECKOUT
            Route::post('/cart/checkout', [
                CartController::class,
                'checkout',
            ])->name('cart.checkout');

            // JUMLAH ITEM CART
            Route::get('/cart/count', [
                CartController::class,
                'getCartCount',
            ])->name('cart.count');

            /*
            |--------------------------------------------------------------------------
            | KONFIRMASI PESANAN
            |--------------------------------------------------------------------------
            | URL: /client/orders/{transaction}
            | File: resources/js/pages/clients/OrderConfirmation.tsx
            */
            Route::post('/orders/{transaction}/cancel', [PaymentController::class, 'cancel'])
                ->name('orders.cancel');

            Route::post('/orders/{transaction}/pay', [PaymentController::class, 'token']);

            Route::get('/orders/{transaction}', function (
                Transaction $transaction,
                MidtransService $midtrans,
            ) {
                abort_unless($transaction->client_id === Auth::id(), 403);

                $transaction = $midtrans->refreshStatus($transaction);

                return Inertia::render('clients/OrderConfirmation', [
                    'transactionId' => $transaction->id,
                    'status'        => $transaction->status,
                    'paymentMethod' => $transaction->payment_method,
                    'total'         => $transaction->total,
                ]);
            })->name('orders.show');
        });

    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->group(function () {

        Route::get('/', function () {
            return Inertia::render('admins/dashboard');
        })->name('dashboard');

        Route::resource('users', AdminUserController::class);
        Route::resource('contacts', AdminContactController::class);
        Route::resource('address', AdminAddressController::class);
        Route::resource('transactions', AdminTransactionController::class);
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('items', AdminItemController::class);
        Route::resource('ratings', AdminRatingController::class);
        Route::resource('details', AdminTransactionDetailController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | COURIER
    |--------------------------------------------------------------------------
    */

    Route::prefix('courier')->group(function () {
        Route::get('/', function () {
            return response()->view('welcome');
        });
    });
});

/*
|--------------------------------------------------------------------------
| SETTINGS & AUTH
|--------------------------------------------------------------------------
*/

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
