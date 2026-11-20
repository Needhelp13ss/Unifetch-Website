<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\ErrandServiceController;
use App\Http\Controllers\RunnerController;
use App\Http\Controllers\Admin;

Route::get('/', function () {
    return view('unifetch');
})->name('home');

Route::get('/unifetch', function () {
    return view('unifetch');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

    Route::get('/register', function () {
        return view('register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        // the admin has their own panel
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::post('/profile/image', [AuthController::class, 'updateProfileImage'])
        ->name('profile.image');

    // ---- NEW: profile CRUD ----
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/image', [ProfileController::class, 'destroyImage'])->name('profile.image.destroy');
    Route::delete('/profile/details', [ProfileController::class, 'clearDetails'])->name('profile.details.clear');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // ---------------------------

    // ---- Customers and runners (the admin can't order) ----
    Route::middleware('shopper')->group(function () {
        Route::get('/menu', [MenuController::class, 'index'])->name('menu');

        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/checkout', [CartController::class, 'select'])->name('cart.checkout');
        Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Custom order: an item that isn't on the menu (part of Ordering)
        Route::get('/custom-order', [CustomOrderController::class, 'create'])->name('custom.create');
        Route::post('/custom-order', [CustomOrderController::class, 'store'])->name('custom.store');

        // Errand Services: ask a runner to get a parcel, wait in a line, etc.
        Route::get('/errand-services', [ErrandServiceController::class, 'create'])->name('errand.create');
        Route::post('/errand-services', [ErrandServiceController::class, 'store'])->name('errand.store');

        // E-wallet
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
        Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');

        // Customers can apply to become a runner (portal ID, course, school ID photo)
        Route::get('/runner/apply', [RunnerController::class, 'applyForm'])->name('runner.apply.form');
        Route::post('/runner/apply', [RunnerController::class, 'apply'])->name('runner.apply');

        // Runner Jobs: approved runners only
        Route::middleware('runner')->prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [RunnerController::class, 'board'])->name('board');
            Route::post('/{order}/accept', [RunnerController::class, 'accept'])->name('accept');
            Route::post('/{order}/release', [RunnerController::class, 'release'])->name('release');
            Route::post('/{order}/start', [RunnerController::class, 'start'])->name('start');
            Route::post('/{order}/complete', [RunnerController::class, 'complete'])->name('complete');
        });
    });

    // ---- Admin panel ----
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/runners', [Admin\RunnerController::class, 'index'])->name('runners');
        Route::get('/runners/{user}/id-photo', [Admin\RunnerController::class, 'idPhoto'])->name('runners.id');
        Route::patch('/runners/{user}/approve', [Admin\RunnerController::class, 'approve'])->name('runners.approve');
        Route::patch('/runners/{user}/reject', [Admin\RunnerController::class, 'reject'])->name('runners.reject');
        Route::patch('/runners/{user}/revoke', [Admin\RunnerController::class, 'revoke'])->name('runners.revoke');

        Route::get('/users', [Admin\UserController::class, 'index'])->name('users');
        Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/wallet', [Admin\UserController::class, 'adjustWallet'])->name('users.wallet');
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/assign', [Admin\OrderController::class, 'assign'])->name('orders.assign');
        Route::post('/orders/{order}/release', [Admin\OrderController::class, 'release'])->name('orders.release');
        Route::post('/orders/{order}/start', [Admin\OrderController::class, 'start'])->name('orders.start');
        Route::post('/orders/{order}/complete', [Admin\OrderController::class, 'complete'])->name('orders.complete');
        Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::delete('/orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('/menu', [Admin\MenuController::class, 'index'])->name('menu');
        Route::post('/vendors', [Admin\MenuController::class, 'storeVendor'])->name('vendors.store');
        Route::get('/vendors/{vendor}', [Admin\MenuController::class, 'showVendor'])->name('vendors.show');
        Route::put('/vendors/{vendor}', [Admin\MenuController::class, 'updateVendor'])->name('vendors.update');
        Route::delete('/vendors/{vendor}', [Admin\MenuController::class, 'destroyVendor'])->name('vendors.destroy');
        Route::post('/vendors/{vendor}/products', [Admin\MenuController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{product}/edit', [Admin\MenuController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{product}', [Admin\MenuController::class, 'updateProduct'])->name('products.update');
        Route::patch('/products/{product}/toggle', [Admin\MenuController::class, 'toggleProduct'])->name('products.toggle');
        Route::delete('/products/{product}', [Admin\MenuController::class, 'destroyProduct'])->name('products.destroy');
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});