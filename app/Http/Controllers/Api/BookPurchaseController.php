<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BadRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookPurchase\BookPurchaseFilterRequest;
use App\Http\Requests\BookPurchase\StoreBookPurchaseRequest;
use App\Http\Requests\BookPurchase\UpdateBookPurchaseRequest;
use App\Http\Resources\BookPurchaseResource;
use App\Services\BookPurchaseService;
use Illuminate\Http\Request;

class BookPurchaseController extends Controller
{
  public function __construct(private BookPurchaseService $bookPurchaseService) {}

  /**
   * Display a listing of book purchases
   * 
   * GET /api/book-purchases
   * Query params: member_id, book_id, start_date, end_date, min_amount, max_amount,
   * order_by, order_direction, with_book, with_member, per_page
   */
  public function index(BookPurchaseFilterRequest $request)
  {
    $purchases = $this->bookPurchaseService->getAllPurchasesPaginate($request->validated());
    return $this->successWithPagination(BookPurchaseResource::collection($purchases));
  }

  /**
   * Store a newly created book purchase
   * 
   * POST /api/book-purchases
   */
  public function store(StoreBookPurchaseRequest $request)
  {
    $purchase = $this->bookPurchaseService->createPurchase($request->validated());
    return $this->created(
      new BookPurchaseResource($purchase),
      'Book purchase created successfully'
    );
  }

  /**
   * Display the specified book purchase
   * 
   * GET /api/book-purchases/{id}
   */
  public function show(string $id)
  {
    $purchase = $this->bookPurchaseService->getPurchase($id);
    return $this->success(new BookPurchaseResource($purchase));
  }

  /**
   * Update the specified book purchase
   * 
   * PUT/PATCH /api/book-purchases/{id}
   */
  public function update(UpdateBookPurchaseRequest $request, string $id)
  {
    $purchase = $this->bookPurchaseService->updatePurchase($id, $request->validated());
    return $this->success(
      new BookPurchaseResource($purchase),
      'Book purchase updated successfully'
    );
  }

  /**
   * Remove the specified book purchase
   * 
   * DELETE /api/book-purchases/{id}
   */
  public function destroy(string $id)
  {
    $this->bookPurchaseService->deletePurchase($id);
    return $this->noContent('Book purchase deleted successfully');
  }

  /**
   * Get all book purchases without pagination
   * 
   * GET /api/book-purchases/all
   */
  public function all(Request $request)
  {
    $purchases = $this->bookPurchaseService->getAll();
    return $this->success(BookPurchaseResource::collection($purchases));
  }

  /**
   * Get purchases for a specific member
   * 
   * GET /api/book-purchases/member/{memberId}
   * Query params: start_date, end_date, order_by, order_direction, per_page
   */
  public function getMemberPurchases(Request $request, string $memberId)
  {
    $request->validate([
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'order_by' => 'nullable|string|in:purchase_date,total_amount,quantity',
      'order_direction' => 'nullable|string|in:asc,desc',
      'per_page' => 'nullable|integer|min:1|max:100',
    ]);

    $purchases = $this->bookPurchaseService->getMemberPurchases(
      $memberId,
      $request->all()
    );

    return $this->successWithPagination(
      BookPurchaseResource::collection($purchases),
      'Member purchases retrieved successfully'
    );
  }

  /**
   * Get purchases for a specific book
   * 
   * GET /api/book-purchases/book/{bookId}
   * Query params: start_date, end_date, order_by, order_direction, per_page
   */
  public function getBookPurchases(Request $request, string $bookId)
  {
    $request->validate([
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'order_by' => 'nullable|string|in:purchase_date,total_amount,quantity',
      'order_direction' => 'nullable|string|in:asc,desc',
      'per_page' => 'nullable|integer|min:1|max:100',
    ]);

    $purchases = $this->bookPurchaseService->getBookPurchases(
      $bookId,
      $request->all()
    );

    return $this->successWithPagination(
      BookPurchaseResource::collection($purchases),
      'Book purchases retrieved successfully'
    );
  }

  /**
   * Get purchase statistics
   * 
   * GET /api/book-purchases/stats
   * Query params: start_date, end_date, book_id
   */
  public function getStats(Request $request)
  {
    $request->validate([
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
      'book_id' => 'nullable|integer|exists:books,id',
    ]);

    $stats = $this->bookPurchaseService->getPurchaseStats($request->all());

    return $this->success(
      $stats,
      'Purchase statistics retrieved successfully'
    );
  }

  /**
   * Get purchase statistics for a specific member
   * 
   * GET /api/book-purchases/member/{memberId}/stats
   * Query params: start_date, end_date
   */
  public function getMemberStats(Request $request, string $memberId)
  {
    $request->validate([
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);

    $stats = $this->bookPurchaseService->getMemberPurchaseStats(
      $memberId,
      $request->all()
    );

    return $this->success(
      $stats,
      'Member purchase statistics retrieved successfully'
    );
  }

  /**
   * Get purchase statistics for a specific book
   * 
   * GET /api/book-purchases/book/{bookId}/stats
   * Query params: start_date, end_date
   */
  public function getBookStats(Request $request, string $bookId)
  {
    $request->validate([
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);

    // For book-specific stats, we'll use the general stats method with book_id filter
    $stats = $this->bookPurchaseService->getPurchaseStats([
      'book_id' => $bookId,
      'start_date' => $request->start_date,
      'end_date' => $request->end_date,
    ]);

    return $this->success(
      $stats,
      'Book purchase statistics retrieved successfully'
    );
  }
}
