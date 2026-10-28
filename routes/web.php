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

Route::get('/menu', [CategoryController::class, 'index']);
Route::get('/menu/{slug}', [CategoryController::class, 'show']);


/*
|--------------------------------------------------------------------------
| PESANAN SAYA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/pesanan-saya', function () {

        $transactions = Transaction::with([
            'details.item'
        ])
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

            return Inertia::render(
                'clients/welcome'
            );

        })->name('home');

    });


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileControllerClient::class,
        'show'
    ])->name('profile.show');

    Route::post('/profile', [
        ProfileControllerClient::class,
        'update'
    ])->name('profile.update');


    /*
    |--------------------------------------------------------------------------
    | CLIENT / CART / CHECKOUT
    |--------------------------------------------------------------------------
    */

    Route::prefix('client')
        ->name('client.')
        ->group(function () {


            /*
            |--------------------------------------------------------------------------
            | CART
            |--------------------------------------------------------------------------
            */

            Route::get('/cart', [
                CartController::class,
                'index'
            ])->name('cart.index');


            /*
            |--------------------------------------------------------------------------
            | TAMBAH KE CART
            |--------------------------------------------------------------------------
            */

            Route::post('/cart/add', [
                CartController::class,
                'addToCart'
            ])->name('cart.add');


            /*
            |--------------------------------------------------------------------------
            | UPDATE CART
            |--------------------------------------------------------------------------
            */

            Route::patch('/cart/update', [
                CartController::class,
                'updateCart'
            ])->name('cart.update');


            /*
            |--------------------------------------------------------------------------
            | HAPUS CART
            |--------------------------------------------------------------------------
            */

            Route::delete('/cart/remove', [
                CartController::class,
                'removeFromCart'
            ])->name('cart.remove');


            /*
            |--------------------------------------------------------------------------
            | CHECKOUT
            |--------------------------------------------------------------------------
            */

            Route::post('/cart/checkout', [
                CartController::class,
                'checkout'
            ])->name('cart.checkout');


            /*
            |--------------------------------------------------------------------------
            | CART COUNT
            |--------------------------------------------------------------------------
            */

            Route::get('/cart/count', [
                CartController::class,
                'getCartCount'
            ])->name('cart.count');


            /*
            |--------------------------------------------------------------------------
            | ORDER CONFIRMATION
            |--------------------------------------------------------------------------
            |
            | Setelah checkout berhasil:
            |
            | /client/cart
            |       ↓
            | POST /client/cart/checkout
            |       ↓
            | CartController@checkout
            |       ↓
            | /client/orders/{transaction}
            |
            */

            Route::get('/orders/{transaction}', function ($transaction) {

                return Inertia::render(
                    'Client/OrderConfirmation',
                    [
                        'transactionId' => (int) $transaction,
                    ]
                );

            })->name('orders.show');

        });


    /*
    |--------------------------------------------------------------------------
    | ADMIN
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')->group(function () {


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/', function () {

            return Inertia::render(
                'admins/dashboard'
            );

        })->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | CRUD ADMIN
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'users',
            AdminUserController::class
        );

        Route::resource(
            'contacts',
            AdminContactController::class
        );

        Route::resource(
            'address',
            AdminAddressController::class
        );

        Route::resource(
            'transactions',
            AdminTransactionController::class
        );

        Route::resource(
            'categories',
            AdminCategoryController::class
        );

        Route::resource(
            'items',
            AdminItemController::class
        );

        Route::resource(
            'ratings',
            AdminRatingController::class
        );

        Route::resource(
            'details',
            AdminTransactionDetailController::class
        );

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