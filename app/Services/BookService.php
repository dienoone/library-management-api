<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookService
{
  public function getAllBooksPaginate(array $filters = [])
  {
    $query = Book::query();

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('isbn', 'like', "%{$search}%")
          ->orWhere('publisher_name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    // Filter by publication year
    if (!empty($filters['publication_year'])) {
      $query->where('publication_year', $filters['publication_year']);
    }

    // Filter by publisher
    if (!empty($filters['publisher'])) {
      $query->where('publisher_name', 'like', "%{$filters['publisher']}%");
    }

    // Filter by borrow availability
    if (isset($filters['can_borrow'])) {
      $canBorrow = filter_var($filters['can_borrow'], FILTER_VALIDATE_BOOLEAN);
      $query->where('can_borrow', $canBorrow);
    }

    // Filter by purchase availability
    if (isset($filters['can_purchase'])) {
      $canPurchase = filter_var($filters['can_purchase'], FILTER_VALIDATE_BOOLEAN);
      $query->where('can_purchase', $canPurchase);
    }

    // Filter by availability (copies in stock)
    if (isset($filters['available'])) {
      $available = filter_var($filters['available'], FILTER_VALIDATE_BOOLEAN);
      if ($available) {
        $query->where('available_copies', '>', 0);
      } else {
        $query->where('available_copies', '<=', 0);
      }
    }

    // Filter by price range
    if (!empty($filters['min_price'])) {
      $query->where('price', '>=', $filters['min_price']);
    }
    if (!empty($filters['max_price'])) {
      $query->where('price', '<=', $filters['max_price']);
    }

    // Filter by author IDs
    if (!empty($filters['author_ids'])) {
      $authorIds = is_array($filters['author_ids']) ? $filters['author_ids'] : explode(',', $filters['author_ids']);
      $query->whereHas('authors', function ($q) use ($authorIds) {
        $q->whereIn('authors.id', $authorIds);
      });
    }

    // Filter by category IDs
    if (!empty($filters['category_ids'])) {
      $categoryIds = is_array($filters['category_ids']) ? $filters['category_ids'] : explode(',', $filters['category_ids']);
      $query->whereHas('categories', function ($q) use ($categoryIds) {
        $q->whereIn('categories.id', $categoryIds);
      });
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'title';
    $orderDirection = $filters['order_direction'] ?? 'asc';

    // Validate order_by field to prevent SQL injection
    $allowedOrderColumns = ['title', 'isbn', 'publisher_name', 'price', 'publication_year', 'publication_date', 'total_copies', 'available_copies', 'created_at'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    } else {
      $query->orderBy('title', 'asc');
    }

    // Load authors relationship if requested
    if (isset($filters['with_authors']) && filter_var($filters['with_authors'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('authors');
    }

    // Load categories relationship if requested
    if (isset($filters['with_categories']) && filter_var($filters['with_categories'], FILTER_VALIDATE_BOOLEAN)) {
      $query->with('categories');
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return Book::orderBy('title', 'asc')->get();
  }

  public function getBook(int $id)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    return $book->load(['authors', 'categories']);
  }

  public function createBook(array $data)
  {
    return DB::transaction(function () use ($data) {
      // Create the book
      $book = Book::create([
        'title' => $data['title'],
        'isbn' => $data['isbn'],
        'description' => $data['description'] ?? null,
        'publisher_name' => $data['publisher_name'],
        'cover_image' => $data['cover_image'] ?? null,
        'total_copies' => $data['total_copies'],
        'available_copies' => $data['available_copies'] ?? $data['total_copies'],
        'price' => $data['price'] ?? null,
        'publication_year' => $data['publication_year'] ?? null,
        'publication_date' => $data['publication_date'] ?? null,
        'can_borrow' => $data['can_borrow'] ?? true,
        'can_purchase' => $data['can_purchase'] ?? false,
      ]);

      // Attach authors if provided
      if (!empty($data['author_ids'])) {
        $book->authors()->attach($data['author_ids']);
      }

      // Attach categories if provided
      if (!empty($data['category_ids'])) {
        $book->categories()->attach($data['category_ids']);
      }

      return $book->load(['authors', 'categories']);
    });
  }

  public function updateBook(int $id, array $data)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    return DB::transaction(function () use ($book, $data) {
      // Update book attributes
      $book->update([
        'title' => $data['title'] ?? $book->title,
        'isbn' => $data['isbn'] ?? $book->isbn,
        'description' => $data['description'] ?? $book->description,
        'publisher_name' => $data['publisher_name'] ?? $book->publisher_name,
        'cover_image' => $data['cover_image'] ?? $book->cover_image,
        'total_copies' => $data['total_copies'] ?? $book->total_copies,
        'available_copies' => $data['available_copies'] ?? $book->available_copies,
        'price' => $data['price'] ?? $book->price,
        'publication_year' => $data['publication_year'] ?? $book->publication_year,
        'publication_date' => $data['publication_date'] ?? $book->publication_date,
        'can_borrow' => $data['can_borrow'] ?? $book->can_borrow,
        'can_purchase' => $data['can_purchase'] ?? $book->can_purchase,
      ]);

      // Sync authors if provided
      if (isset($data['author_ids'])) {
        $book->authors()->sync($data['author_ids']);
      }

      // Sync categories if provided
      if (isset($data['category_ids'])) {
        $book->categories()->sync($data['category_ids']);
      }

      return $book->load(['authors', 'categories']);
    });
  }

  public function deleteBook(int $id)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    throw_if(
      $book->borrowings()->exists() || $book->purchases()->exists(),
      ConflictException::class,
      'Cannot delete book with associated borrowings or purchases. Please remove associated records first.'
    );

    return DB::transaction(function () use ($book) {
      // Detach all relationships
      $book->authors()->detach();
      $book->categories()->detach();

      $book->delete();
    });
  }

  public function search(string $search)
  {
    return Book::where('title', 'like', "%{$search}%")
      ->orWhere('isbn', 'like', "%{$search}%")
      ->orWhere('publisher_name', 'like', "%{$search}%")
      ->orWhere('description', 'like', "%{$search}%")
      ->orderBy('title', 'asc')
      ->get();
  }

  public function attachAuthors(int $id, array $data)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    $book->authors()->syncWithoutDetaching($data['author_ids']);

    return $book->load(['authors', 'categories']);
  }

  public function detachAuthors(int $id, array $data)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    $book->authors()->detach($data['author_ids']);

    return $book->load(['authors', 'categories']);
  }

  public function attachCategories(int $id, array $data)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    $book->categories()->syncWithoutDetaching($data['category_ids']);

    return $book->load(['authors', 'categories']);
  }

  public function detachCategories(int $id, array $data)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    $book->categories()->detach($data['category_ids']);

    return $book->load(['authors', 'categories']);
  }

  public function getAvailableBooks()
  {
    return Book::where('available_copies', '>', 0)
      ->where('can_borrow', true)
      ->orderBy('title', 'asc')
      ->get();
  }

  public function getBooksForPurchase()
  {
    return Book::where('can_purchase', true)
      ->where('price', '>', 0)
      ->orderBy('title', 'asc')
      ->get();
  }

  public function updateBookCopies(int $id, int $newTotalCopies)
  {
    $book = Book::find($id);
    throw_if(!$book, NotFoundException::class, 'Book not found');

    $currentAvailable = $book->available_copies;
    $difference = $newTotalCopies - $book->total_copies;

    $book->update([
      'total_copies' => $newTotalCopies,
      'available_copies' => max(0, $currentAvailable + $difference)
    ]);

    return $book;
  }
}
