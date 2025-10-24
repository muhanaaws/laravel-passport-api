<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/login', [App\Http\Controllers\Auth\ApiPassportController::class, 'login']);
Route::post('/refresh', [App\Http\Controllers\Auth\ApiPassportController::class, 'refresh']);
