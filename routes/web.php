<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

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

    // ---- Food ordering: menu, cart, checkout, orders ----
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
    // -----------------------------------------------------

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});