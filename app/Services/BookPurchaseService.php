<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\BookPurchase;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class BookPurchaseService
{
  public function getAllPurchasesPaginate(array $filters = [])
  {
    $query = BookPurchase::query();

    // Filter by member ID
    if (!empty($filters['member_id'])) {
      $query->where('member_id', $filters['member_id']);
    }

    // Filter by book ID
    if (!empty($filters['book_id'])) {
      $query->where('book_id', $filters['book_id']);
    }

    // Filter by purchase date range
    if (!empty($filters['start_date'])) {
      $query->where('purchase_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->where('purchase_date', '<=', $filters['end_date']);
    }

    // Filter by minimum total amount
    if (!empty($filters['min_amount'])) {
      $query->where('total_amount', '>=', $filters['min_amount']);
    }

    // Filter by maximum total amount
    if (!empty($filters['max_amount'])) {
      $query->where('total_amount', '<=', $filters['max_amount']);
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'purchase_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    // Validate order_by field to prevent SQL injection
    $allowedOrderColumns = ['purchase_date', 'total_amount', 'quantity', 'unit_price', 'created_at'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    } else {
      $query->orderBy('purchase_date', 'desc');
    }

    // Load relationships if requested
    if (isset($filters['with_book']) && filter_var($filters['with_book'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('book');
    }

    if (isset($filters['with_member']) && filter_var($filters['with_member'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('member');
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return BookPurchase::with(['book', 'member'])
      ->orderBy('purchase_date', 'desc')
      ->get();
  }

  public function getPurchase(int $id)
  {
    $purchase = BookPurchase::with(['book', 'member'])->find($id);
    throw_if(!$purchase, NotFoundException::class, 'Book purchase not found');

    return $purchase;
  }

  public function createPurchase(array $data)
  {
    return DB::transaction(function () use ($data) {
      // Verify book exists and can be purchased
      $book = Book::find($data['book_id']);
      throw_if(!$book, NotFoundException::class, 'Book not found');
      throw_if(!$book->can_purchase, ConflictException::class, 'This book is not available for purchase');

      // Verify member exists
      $member = Member::find($data['member_id']);
      throw_if(!$member, NotFoundException::class, 'Member not found');

      // Calculate total amount if not provided
      $unitPrice = $book->price;
      $quantity = $data['quantity'] ?? 1;
      $totalAmount = $data['total_amount'] ?? ($unitPrice * $quantity);

      // Create the purchase
      $purchase = BookPurchase::create([
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'total_amount' => $totalAmount,
        'purchase_date' => $data['purchase_date'] ?? now(),
        'book_id' => $data['book_id'],
        'member_id' => $data['member_id'],
      ]);

      return $purchase->load(['book', 'member']);
    });
  }

  public function updatePurchase(int $id, array $data)
  {
    $purchase = BookPurchase::find($id);
    throw_if(!$purchase, NotFoundException::class, 'Book purchase not found');

    return DB::transaction(function () use ($purchase, $data) {
      // If book_id is being updated, verify new book exists and can be purchased
      if (isset($data['book_id']) && $data['book_id'] != $purchase->book_id) {
        $book = Book::find($data['book_id']);
        throw_if(!$book, NotFoundException::class, 'Book not found');
        throw_if(!$book->can_purchase, ConflictException::class, 'This book is not available for purchase');
      }

      // If member_id is being updated, verify new member exists
      if (isset($data['member_id']) && $data['member_id'] != $purchase->member_id) {
        $member = Member::find($data['member_id']);
        throw_if(!$member, NotFoundException::class, 'Member not found');
      }

      // Recalculate total amount if quantity or unit_price changes
      $quantity = $data['quantity'] ?? $purchase->quantity;
      $unitPrice = $data['unit_price'] ?? $purchase->unit_price;

      if (isset($data['quantity']) || isset($data['unit_price'])) {
        $data['total_amount'] = $quantity * $unitPrice;
      }

      $purchase->update([
        'quantity' => $data['quantity'] ?? $purchase->quantity,
        'unit_price' => $data['unit_price'] ?? $purchase->unit_price,
        'total_amount' => $data['total_amount'] ?? $purchase->total_amount,
        'purchase_date' => $data['purchase_date'] ?? $purchase->purchase_date,
        'book_id' => $data['book_id'] ?? $purchase->book_id,
        'member_id' => $data['member_id'] ?? $purchase->member_id,
      ]);

      return $purchase->load(['book', 'member']);
    });
  }

  public function deletePurchase(int $id)
  {
    $purchase = BookPurchase::find($id);
    throw_if(!$purchase, NotFoundException::class, 'Book purchase not found');

    return $purchase->delete();
  }

  public function getMemberPurchases(int $memberId, array $filters = [])
  {
    $query = BookPurchase::where('member_id', $memberId);

    // Filter by purchase date range
    if (!empty($filters['start_date'])) {
      $query->where('purchase_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->where('purchase_date', '<=', $filters['end_date']);
    }

    // Load book relationship by default for member purchases
    $query->with('book');

    $orderBy = $filters['order_by'] ?? 'purchase_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    $allowedOrderColumns = ['purchase_date', 'total_amount', 'quantity'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getBookPurchases(int $bookId, array $filters = [])
  {
    $query = BookPurchase::where('book_id', $bookId);

    // Filter by purchase date range
    if (!empty($filters['start_date'])) {
      $query->where('purchase_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->where('purchase_date', '<=', $filters['end_date']);
    }

    // Load member relationship by default for book purchases
    $query->with('member');

    $orderBy = $filters['order_by'] ?? 'purchase_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    $allowedOrderColumns = ['purchase_date', 'total_amount', 'quantity'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getPurchaseStats(array $filters = [])
  {
    $query = BookPurchase::query();

    // Filter by date range
    if (!empty($filters['start_date'])) {
      $query->where('purchase_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->where('purchase_date', '<=', $filters['end_date']);
    }

    // Filter by book
    if (!empty($filters['book_id'])) {
      $query->where('book_id', $filters['book_id']);
    }

    return [
      'total_purchases' => $query->count(),
      'total_quantity' => $query->sum('quantity'),
      'total_revenue' => $query->sum('total_amount'),
      'average_purchase_value' => $query->avg('total_amount'),
    ];
  }

  public function getMemberPurchaseStats(int $memberId, array $filters = [])
  {
    $query = BookPurchase::where('member_id', $memberId);

    // Filter by date range
    if (!empty($filters['start_date'])) {
      $query->where('purchase_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->where('purchase_date', '<=', $filters['end_date']);
    }

    return [
      'total_purchases' => $query->count(),
      'total_books' => $query->sum('quantity'),
      'total_spent' => $query->sum('total_amount'),
      'average_purchase_value' => $query->avg('total_amount'),
    ];
  }
}
