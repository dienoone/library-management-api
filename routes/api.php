<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookPurchaseController;

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


/*
|--------------------------------------------------------------------------
| Author Routes
|--------------------------------------------------------------------------
*/

Route::prefix('authors')->group(function () {
    Route::get('/', [AuthorController::class, 'index']);
    Route::get('/all', [AuthorController::class, 'all']);
    Route::get('/search', [AuthorController::class, 'search']);
    Route::get('/{id}', [AuthorController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('authors')->group(function () {
    // Standard CRUD operations
    Route::put('/{id}', [AuthorController::class, 'update']);
    Route::patch('/{id}', [AuthorController::class, 'update']);
    Route::delete('/{id}', [AuthorController::class, 'destroy']);

    // Book associations
    Route::post('/{id}/books', [AuthorController::class, 'attachBooks']);
    Route::delete('/{id}/books', [AuthorController::class, 'detachBooks']);
});

/*
|--------------------------------------------------------------------------
| Book Routes
|--------------------------------------------------------------------------
*/

Route::prefix('books')->group(function () {
    // Public routes
    Route::get('/', [BookController::class, 'index']);
    Route::get('/all', [BookController::class, 'all']);
    Route::get('/search', [BookController::class, 'search']);
    Route::get('/available', [BookController::class, 'available']);
    Route::get('/for-purchase', [BookController::class, 'forPurchase']);
    Route::get('/{id}', [BookController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('books')->group(function () {
    // Standard CRUD operations
    Route::post('/', [BookController::class, 'store']);
    Route::put('/{id}', [BookController::class, 'update']);
    Route::patch('/{id}', [BookController::class, 'update']);
    Route::delete('/{id}', [BookController::class, 'destroy']);

    // Book copies management
    Route::patch('/{id}/copies', [BookController::class, 'updateCopies']);

    // Author associations
    Route::post('/{id}/authors', [BookController::class, 'attachAuthors']);
    Route::delete('/{id}/authors', [BookController::class, 'detachAuthors']);

    // Category associations
    Route::post('/{id}/categories', [BookController::class, 'attachCategories']);
    Route::delete('/{id}/categories', [BookController::class, 'detachCategories']);
});

/*
|--------------------------------------------------------------------------
| Book Purchase Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('book-purchases')->group(function () {
    // Standard CRUD operations
    Route::get('/', [BookPurchaseController::class, 'index']);
    Route::post('/', [BookPurchaseController::class, 'store']);
    Route::get('/all', [BookPurchaseController::class, 'all']);
    Route::get('/{id}', [BookPurchaseController::class, 'show']);
    Route::put('/{id}', [BookPurchaseController::class, 'update']);
    Route::patch('/{id}', [BookPurchaseController::class, 'update']);
    Route::delete('/{id}', [BookPurchaseController::class, 'destroy']);

    // Additional custom routes
    Route::get('/member/{memberId}', [BookPurchaseController::class, 'getMemberPurchases']);
    Route::get('/book/{bookId}', [BookPurchaseController::class, 'getBookPurchases']);
    Route::get('/stats/overall', [BookPurchaseController::class, 'getStats']);
    Route::get('/stats/member/{memberId}', [BookPurchaseController::class, 'getMemberStats']);
    Route::get('/stats/book/{bookId}', [BookPurchaseController::class, 'getBookStats']);
});
