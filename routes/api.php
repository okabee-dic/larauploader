<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileController;


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout')->middleware(['auth'])->name('api_logout');
    Route::post('refresh', 'refresh')->middleware(['auth'])->name('api_refresh');
});

Route::middleware(['auth'])->group(function(){
    Route::post('create_user', 'App\Http\Controllers\AdminController@create_user');

    Route::post('file', 'App\Http\Controllers\FileController@create');
    Route::post('file/{id}', 'App\Http\Controllers\FileController@show');
    Route::delete('file/{id}', 'App\Http\Controllers\FileController@destroy');    
});

