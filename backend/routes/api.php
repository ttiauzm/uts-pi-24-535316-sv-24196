<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FilmController;
use Illuminate\Support\Facades\Route;

// Satu-satunya endpoint publik: login (mengembalikan token Sanctum).
Route::post('/login', [AuthController::class, 'login']);

// Seluruh endpoint lain wajib header "Authorization: Bearer <token>".
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);

    // CRUD RESTful: index, store, show, update, destroy.
    Route::apiResource('films', FilmController::class);
});
