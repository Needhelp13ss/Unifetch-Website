
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
    
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
