<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookPurchaseController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\LibrarianController;
use App\Http\Controllers\Api\MemberController;

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

/*
|--------------------------------------------------------------------------
| Borrowing Routes
|--------------------------------------------------------------------------
*/

Route::prefix('borrowings')->group(function () {
    // Public routes - minimal public access if any
    Route::get('/search', [BorrowingController::class, 'search']);
});

Route::middleware('auth:sanctum')->prefix('borrowings')->group(function () {
    // Standard CRUD operations
    Route::get('/', [BorrowingController::class, 'index']);
    Route::post('/', [BorrowingController::class, 'store']);
    Route::get('/all', [BorrowingController::class, 'all']);
    Route::get('/{id}', [BorrowingController::class, 'show']);
    Route::put('/{id}', [BorrowingController::class, 'update']);
    Route::patch('/{id}', [BorrowingController::class, 'update']);
    Route::delete('/{id}', [BorrowingController::class, 'destroy']);

    // Additional custom routes
    Route::get('/member/{memberId}', [BorrowingController::class, 'byMember']);
    Route::get('/book/{bookId}', [BorrowingController::class, 'byBook']);
    Route::get('/overdue', [BorrowingController::class, 'overdue']);
    Route::get('/active', [BorrowingController::class, 'active']);
    Route::get('/statistics', [BorrowingController::class, 'statistics']);

    // Borrowing actions
    Route::post('/{id}/renew', [BorrowingController::class, 'renew']);
    Route::post('/{id}/return', [BorrowingController::class, 'return']);
    Route::post('/update-overdue-status', [BorrowingController::class, 'updateOverdueStatus']);
});

// User-specific borrowing routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/my-borrowings', [BorrowingController::class, 'myBorrowings']);
});

/*
|--------------------------------------------------------------------------
| Librarian Routes
|--------------------------------------------------------------------------
*/

Route::prefix('librarians')->group(function () {
    Route::get('/', [LibrarianController::class, 'index']);
    Route::get('/all', [LibrarianController::class, 'all']);
    Route::get('/search', [LibrarianController::class, 'search']);
    Route::get('/{id}', [LibrarianController::class, 'show']);
});

Route::middleware('auth:sanctum')->prefix('librarians')->group(function () {
    // Standard CRUD operations
    Route::put('/{id}', [LibrarianController::class, 'update']);
    Route::patch('/{id}', [LibrarianController::class, 'update']);
    Route::delete('/{id}', [LibrarianController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
*/

Route::prefix('members')->group(function () {
    Route::get('/', [MemberController::class, 'index']);
    Route::get('/all', [MemberController::class, 'all']);
    Route::get('/search', [MemberController::class, 'search']);
    Route::get('/{id}', [MemberController::class, 'show']);
    Route::get('/{id}/borrowings', [MemberController::class, 'getBorrowings']);
    Route::get('/{id}/active-borrowings-count', [MemberController::class, 'getActiveBorrowingsCount']);
    Route::get('/{id}/can-borrow', [MemberController::class, 'canBorrow']);
});

Route::middleware('auth:sanctum')->prefix('members')->group(function () {
    // Standard CRUD operations
    Route::put('/{id}', [MemberController::class, 'update']);
    Route::patch('/{id}', [MemberController::class, 'update']);
    Route::delete('/{id}', [MemberController::class, 'destroy']);

    // Status management
    Route::patch('/{id}/status', [MemberController::class, 'updateStatus']);
});
