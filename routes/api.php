<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

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



/*
|--------------------------------------------------------------------------
| Category Routes
|--------------------------------------------------------------------------
*/

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/all', [CategoryController::class, 'all']);
    Route::get('/search', [CategoryController::class, 'search']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('categories')->group(function () {
    // Standard CRUD operations
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::patch('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);

    // Book associations
    Route::post('/{id}/books', [CategoryController::class, 'attachBooks']);
    Route::delete('/{id}/books', [CategoryController::class, 'detachBooks']);
});
