<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function(){
    // ログインしていなかった時のリダイレクト先
    return response()->json([], 401, ['Content-Type' => 'application/json'], JSON_UNESCAPED_UNICODE);
})->name('login');