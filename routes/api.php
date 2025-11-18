<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    // Public routes
    Route::post('/register/author', [AuthController::class, 'registerAuthor']);
    Route::post('/register/member', [AuthController::class, 'registerMember']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        // Admin only - register librarian
        Route::post('/register/librarian', [AuthController::class, 'registerLibrarian']);
    });
});
