<?php

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ApiResponse::success([
        'status' => 'ok',
]));

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
