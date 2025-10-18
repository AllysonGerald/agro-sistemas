<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    echo "API is running";
});

Route::get('/teste', function () {
    // return view('welcome');
    return view('emails.password-reset');
});
