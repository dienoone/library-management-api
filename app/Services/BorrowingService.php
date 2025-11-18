<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Borrowing;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
  public function getAllBorrowingsPaginate(array $filters = [])
  {
    $query = Borrowing::query();

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->whereHas('book', function ($bookQuery) use ($search) {
          $bookQuery->where('title', 'like', "%{$search}%")
            ->orWhere('isbn', 'like', "%{$search}%");
        })
          ->orWhereHas('member', function ($memberQuery) use ($search) {
            $memberQuery->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
          })
          ->orWhere('notes', 'like', "%{$search}%");
      });
    }

    // Filter by status
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    // Filter by member ID
    if (!empty($filters['member_id'])) {
      $query->where('member_id', $filters['member_id']);
    }

    // Filter by book ID
    if (!empty($filters['book_id'])) {
      $query->where('book_id', $filters['book_id']);
    }

    // Filter by overdue borrowings
    if (isset($filters['overdue'])) {
      $overdue = filter_var($filters['overdue'], FILTER_VALIDATE_BOOLEAN);
      if ($overdue) {
        $query->where('status', 'borrowed')
          ->where('due_date', '<', now());
      }
    }

    // Filter by date range
    if (!empty($filters['borrow_date_from'])) {
      $query->where('borrow_date', '>=', $filters['borrow_date_from']);
    }
    if (!empty($filters['borrow_date_to'])) {
      $query->where('borrow_date', '<=', $filters['borrow_date_to']);
    }
    if (!empty($filters['due_date_from'])) {
      $query->where('due_date', '>=', $filters['due_date_from']);
    }
    if (!empty($filters['due_date_to'])) {
      $query->where('due_date', '<=', $filters['due_date_to']);
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'borrow_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    // Validate order_by field to prevent SQL injection
    $allowedOrderColumns = ['borrow_date', 'due_date', 'return_date', 'status', 'renewal_count', 'created_at'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    } else {
      $query->orderBy('borrow_date', 'desc');
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
    return Borrowing::with(['book', 'member'])
      ->orderBy('borrow_date', 'desc')
      ->get();
  }

  public function getBorrowing(int $id)
  {
    $borrowing = Borrowing::with(['book', 'member'])->find($id);
    throw_if(!$borrowing, NotFoundException::class, 'Borrowing record not found');

    return $borrowing;
  }

  public function createBorrowing(array $data)
  {
    return DB::transaction(function () use ($data) {
      // Check if book exists and is available
      $book = Book::find($data['book_id']);
      throw_if(!$book, NotFoundException::class, 'Book not found');

      // Check if member exists
      $member = Member::find($data['member_id']);
      throw_if(!$member, NotFoundException::class, 'Member not found');

      // Check if book can be borrowed
      throw_if(
        !$book->can_borrow,
        ConflictException::class,
        'This book cannot be borrowed'
      );

      // Check if book has available copies
      throw_if(
        $book->available_copies <= 0,
        ConflictException::class,
        'No available copies of this book'
      );

      // Check if member already has this book borrowed and not returned
      $existingBorrowing = Borrowing::where('member_id', $data['member_id'])
        ->where('book_id', $data['book_id'])
        ->where('status', 'borrowed')
        ->first();

      throw_if(
        $existingBorrowing,
        ConflictException::class,
        'Member already has this book borrowed'
      );

      // Calculate due date if not provided (default 14 days)
      $borrowDate = $data['borrow_date'] ?? now();
      $dueDate = $data['due_date'] ?? $borrowDate->copy()->addDays(14);

      // Create the borrowing record
      $borrowing = Borrowing::create([
        'borrow_date' => $borrowDate,
        'due_date' => $dueDate,
        'return_date' => null,
        'status' => 'borrowed',
        'renewal_count' => 0,
        'notes' => $data['notes'] ?? null,
        'book_id' => $data['book_id'],
        'member_id' => $data['member_id'],
      ]);

      // Update book available copies
      $book->decrement('available_copies');

      return $borrowing->load(['book', 'member']);
    });
  }

  public function updateBorrowing(int $id, array $data)
  {
    $borrowing = Borrowing::find($id);
    throw_if(!$borrowing, NotFoundException::class, 'Borrowing record not found');

    return DB::transaction(function () use ($borrowing, $data) {
      $originalStatus = $borrowing->status;
      $newStatus = $data['status'] ?? $borrowing->status;

      // Update borrowing record
      $borrowing->update([
        'borrow_date' => $data['borrow_date'] ?? $borrowing->borrow_date,
        'due_date' => $data['due_date'] ?? $borrowing->due_date,
        'return_date' => $data['return_date'] ?? $borrowing->return_date,
        'status' => $newStatus,
        'renewal_count' => $data['renewal_count'] ?? $borrowing->renewal_count,
        'notes' => $data['notes'] ?? $borrowing->notes,
      ]);

      // Handle status changes
      if ($originalStatus !== $newStatus) {
        if ($newStatus === 'returned' && $originalStatus === 'borrowed') {
          // Book is being returned - increment available copies
          $borrowing->book->increment('available_copies');
        } elseif ($newStatus === 'borrowed' && $originalStatus === 'returned') {
          // Book is being borrowed again - decrement available copies
          $borrowing->book->decrement('available_copies');
        }
      }

      return $borrowing->load(['book', 'member']);
    });
  }

  public function deleteBorrowing(int $id)
  {
    $borrowing = Borrowing::find($id);
    throw_if(!$borrowing, NotFoundException::class, 'Borrowing record not found');

    return DB::transaction(function () use ($borrowing) {
      // If the book is currently borrowed, return it first
      if ($borrowing->status === 'borrowed') {
        $borrowing->book->increment('available_copies');
      }

      $borrowing->delete();
    });
  }

  public function renewBorrowing(int $id)
  {
    $borrowing = Borrowing::with(['book'])->find($id);
    throw_if(!$borrowing, NotFoundException::class, 'Borrowing record not found');

    throw_if(
      !$borrowing->canRenew(),
      ConflictException::class,
      'This borrowing cannot be renewed. It may be overdue, already returned, or has reached the maximum renewal limit.'
    );

    return DB::transaction(function () use ($borrowing) {
      // Extend due date by 14 days
      $newDueDate = $borrowing->due_date->copy()->addDays(14);

      $borrowing->update([
        'due_date' => $newDueDate,
        'renewal_count' => $borrowing->renewal_count + 1,
      ]);

      return $borrowing->load(['book', 'member']);
    });
  }

  public function returnBorrowing(int $id, array $data = [])
  {
    $borrowing = Borrowing::with(['book'])->find($id);
    throw_if(!$borrowing, NotFoundException::class, 'Borrowing record not found');

    throw_if(
      $borrowing->status === 'returned',
      ConflictException::class,
      'This book has already been returned'
    );

    return DB::transaction(function () use ($borrowing, $data) {
      $returnDate = $data['return_date'] ?? now();

      $borrowing->update([
        'return_date' => $returnDate,
        'status' => 'returned',
        'notes' => $data['notes'] ?? $borrowing->notes,
      ]);

      // Return the book - increment available copies
      $borrowing->book->returnBook();

      return $borrowing->load(['book', 'member']);
    });
  }

  public function getMemberBorrowings(int $memberId, array $filters = [])
  {
    $query = Borrowing::where('member_id', $memberId);

    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    if (isset($filters['active'])) {
      $active = filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN);
      if ($active) {
        $query->where('status', 'borrowed');
      } else {
        $query->where('status', '!=', 'borrowed');
      }
    }

    $query->with('book')
      ->orderBy('borrow_date', 'desc');

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getBookBorrowings(int $bookId, array $filters = [])
  {
    $query = Borrowing::where('book_id', $bookId);

    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    $query->with('member')
      ->orderBy('borrow_date', 'desc');

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getOverdueBorrowings()
  {
    return Borrowing::where('status', 'borrowed')
      ->where('due_date', '<', now())
      ->with(['book', 'member'])
      ->orderBy('due_date', 'asc')
      ->get();
  }

  public function getActiveBorrowings()
  {
    return Borrowing::where('status', 'borrowed')
      ->with(['book', 'member'])
      ->orderBy('due_date', 'asc')
      ->get();
  }

  public function updateOverdueStatus()
  {
    return Borrowing::where('status', 'borrowed')
      ->where('due_date', '<', now())
      ->update(['status' => 'overdue']);
  }
}
