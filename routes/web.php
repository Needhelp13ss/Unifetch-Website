
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('unifetch');
})->name('home');

Route::get('/unifetch', function () {
    return view('unifetch');
});

// Login page
Route::get('/login', function () {
    return view('login');
})->name('login');

// Registration page
Route::get('/register', function () {
    return view('register');
})->name('register');
