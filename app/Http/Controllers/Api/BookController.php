<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Book\AttachAuthorsRequest;
use App\Http\Requests\Book\AttachCategoriesRequest;
use App\Http\Requests\Book\DetachAuthorsRequest;
use App\Http\Requests\Book\DetachCategoriesRequest;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private BookService $bookService) {}

    /**
     * Display a listing of books
     * 
     * GET /api/books
     * Query params: search, order_by, order_direction, per_page, with_authors, with_categories, 
     * publication_year, publisher, can_borrow, can_purchase, available, min_price, max_price,
     * author_ids, category_ids
     */
    public function index(Request $request)
    {
        $books = $this->bookService->getAllBooksPaginate($request->all());
        return $this->successWithPagination(BookResource::collection($books));
    }

    /**
     * Store a newly created book
     * 
     * POST /api/books
     */
    public function store(StoreBookRequest $request)
    {
        $book = $this->bookService->createBook($request->validated());
        return $this->created(
            new BookResource($book),
            'Book created successfully'
        );
    }

    /**
     * Display the specified book
     * 
     * GET /api/books/{id}
     */
    public function show(string $id)
    {
        $book = $this->bookService->getBook($id);
        return $this->success(new BookResource($book));
    }

    /**
     * Update the specified book
     * 
     * PUT/PATCH /api/books/{id}
     */
    public function update(UpdateBookRequest $request, string $id)
    {
        $book = $this->bookService->updateBook($id, $request->validated());
        return $this->success(new BookResource($book), 'Book updated successfully');
    }

    /**
     * Remove the specified book
     * 
     * DELETE /api/books/{id}
     */
    public function destroy(string $id)
    {
        $this->bookService->deleteBook($id);
        return $this->noContent('Book deleted successfully');
    }

    /**
     * Get all books without pagination
     * 
     * GET /api/books/all
     */
    public function all(Request $request)
    {
        $books = $this->bookService->getAll();
        return $this->success(BookResource::collection($books));
    }

    /**
     * Search books
     * 
     * GET /api/books/search?q={query}
     */
    public function search(Request $request)
    {
        $query = $request->query('q', '');
        throw_if(
            empty($query),
            BadRequestException::class,
            'Search query is required'
        );

        $books = $this->bookService->search($query);

        return $this->success(
            BookResource::collection($books),
            'Search completed successfully'
        );
    }

    /**
     * Attach authors to book
     * 
     * POST /api/books/{id}/authors
     * Body: { "author_ids": [1, 2, 3] }
     */
    public function attachAuthors(AttachAuthorsRequest $request, int $id)
    {
        $book = $this->bookService->attachAuthors($id, $request->validated());
        return $this->success(
            new BookResource($book),
            'Authors attached to book successfully'
        );
    }

    /**
     * Detach authors from book
     * 
     * DELETE /api/books/{id}/authors
     * Body: { "author_ids": [1, 2, 3] }
     */
    public function detachAuthors(DetachAuthorsRequest $request, int $id)
    {
        $book = $this->bookService->detachAuthors($id, $request->validated());
        return $this->success(
            new BookResource($book),
            'Authors detached from book successfully'
        );
    }

    /**
     * Attach categories to book
     * 
     * POST /api/books/{id}/categories
     * Body: { "category_ids": [1, 2, 3] }
     */
    public function attachCategories(AttachCategoriesRequest $request, int $id)
    {
        $book = $this->bookService->attachCategories($id, $request->validated());
        return $this->success(
            new BookResource($book),
            'Categories attached to book successfully'
        );
    }

    /**
     * Detach categories from book
     * 
     * DELETE /api/books/{id}/categories
     * Body: { "category_ids": [1, 2, 3] }
     */
    public function detachCategories(DetachCategoriesRequest $request, int $id)
    {
        $book = $this->bookService->detachCategories($id, $request->validated());
        return $this->success(
            new BookResource($book),
            'Categories detached from book successfully'
        );
    }

    /**
     * Get available books for borrowing
     * 
     * GET /api/books/available
     */
    public function available()
    {
        $books = $this->bookService->getAvailableBooks();
        return $this->success(
            BookResource::collection($books),
            'Available books retrieved successfully'
        );
    }

    /**
     * Get books available for purchase
     * 
     * GET /api/books/for-purchase
     */
    public function forPurchase()
    {
        $books = $this->bookService->getBooksForPurchase();
        return $this->success(
            BookResource::collection($books),
            'Books for purchase retrieved successfully'
        );
    }

    /**
     * Update book copies
     * 
     * PATCH /api/books/{id}/copies
     * Body: { "total_copies": 10 }
     */
    public function updateCopies(Request $request, string $id)
    {
        $request->validate([
            'total_copies' => 'required|integer|min:0'
        ]);

        $book = $this->bookService->updateBookCopies($id, $request->total_copies);
        return $this->success(
            new BookResource($book),
            'Book copies updated successfully'
        );
    }
}
