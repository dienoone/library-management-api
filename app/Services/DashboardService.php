<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Librarian;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
  /**
   * Get comprehensive dashboard statistics
   *
   * @return array
   */
  public function getStatistics(): array
  {
    return [
      'overview' => $this->getOverviewStats(),
      'users' => $this->getUserStats(),
      'books' => $this->getBookStats(),
      'borrowings' => $this->getBorrowingStats(),
      'purchases' => $this->getPurchaseStats(),
      'recent_activities' => $this->getRecentActivities(),
      'charts' => $this->getChartData(),
    ];
  }

  /**
   * Get overview statistics
   *
   * @return array
   */
  protected function getOverviewStats(): array
  {
    return [
      'total_users' => User::count(),
      'total_members' => Member::count(),
      'total_authors' => Author::count(),
      'total_librarians' => Librarian::count(),
      'total_books' => Book::count(),
      'total_categories' => Category::count(),
      'active_borrowings' => Borrowing::whereIn('status', ['borrowed', 'overdue'])->count(),
      'overdue_borrowings' => Borrowing::where('status', 'overdue')->count(),
      'total_purchases' => BookPurchase::count(),
      'total_revenue' => BookPurchase::sum('total_amount'),
    ];
  }

  /**
   * Get user statistics
   *
   * @return array
   */
  protected function getUserStats(): array
  {
    $membersByStatus = Member::select('status', DB::raw('count(*) as count'))
      ->groupBy('status')
      ->get()
      ->pluck('count', 'status')
      ->toArray();

    return [
      'total_users' => User::count(),
      'members' => [
        'total' => Member::count(),
        'active' => $membersByStatus['active'] ?? 0,
        'inactive' => $membersByStatus['inactive'] ?? 0,
        'suspended' => $membersByStatus['suspended'] ?? 0,
      ],
      'authors' => [
        'total' => Author::count(),
        'with_books' => Author::has('books')->count(),
      ],
      'librarians' => [
        'total' => Librarian::count(),
      ],
      'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
    ];
  }

  /**
   * Get book statistics
   *
   * @return array
   */
  protected function getBookStats(): array
  {
    return [
      'total_books' => Book::count(),
      'total_copies' => Book::sum('total_copies'),
      'available_copies' => Book::sum('available_copies'),
      'borrowed_copies' => Book::sum(DB::raw('total_copies - available_copies')),
      'borrowable_books' => Book::where('can_borrow', true)->count(),
      'purchasable_books' => Book::where('can_purchase', true)->count(),
      'out_of_stock' => Book::where('available_copies', 0)->count(),
      'low_stock' => Book::whereRaw('available_copies > 0 AND available_copies <= 5')->count(),
    ];
  }

  /**
   * Get borrowing statistics
   *
   * @return array
   */
  protected function getBorrowingStats(): array
  {
    $borrowingsByStatus = Borrowing::select('status', DB::raw('count(*) as count'))
      ->groupBy('status')
      ->get()
      ->pluck('count', 'status')
      ->toArray();

    return [
      'total_borrowings' => Borrowing::count(),
      'borrowed' => $borrowingsByStatus['borrowed'] ?? 0,
      'returned' => $borrowingsByStatus['returned'] ?? 0,
      'overdue' => $borrowingsByStatus['overdue'] ?? 0,
      'this_month' => Borrowing::whereMonth('borrow_date', now()->month)
        ->whereYear('borrow_date', now()->year)
        ->count(),
      'today' => Borrowing::whereDate('borrow_date', now())->count(),
      'due_soon' => Borrowing::where('status', 'borrowed')
        ->whereBetween('due_date', [now(), now()->addDays(7)])
        ->count(),
    ];
  }

  /**
   * Get purchase statistics
   *
   * @return array
   */
  protected function getPurchaseStats(): array
  {
    return [
      'total_purchases' => BookPurchase::count(),
      'total_revenue' => BookPurchase::sum('total_amount'),
      'total_quantity_sold' => BookPurchase::sum('quantity'),
      'this_month_revenue' => BookPurchase::whereMonth('purchase_date', now()->month)
        ->whereYear('purchase_date', now()->year)
        ->sum('total_amount'),
      'this_month_purchases' => BookPurchase::whereMonth('purchase_date', now()->month)
        ->whereYear('purchase_date', now()->year)
        ->count(),
      'today_revenue' => BookPurchase::whereDate('purchase_date', now())
        ->sum('total_amount'),
      'average_purchase_value' => BookPurchase::avg('total_amount'),
    ];
  }

  /**
   * Get recent activities
   *
   * @return array
   */
  protected function getRecentActivities(): array
  {
    return [
      'recent_borrowings' => Borrowing::with(['book', 'member.user'])
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($borrowing) {
          return [
            'id' => $borrowing->id,
            'type' => 'borrowing',
            'book_title' => $borrowing->book->title,
            'member_name' => $borrowing->member->name,
            'status' => $borrowing->status,
            'date' => $borrowing->borrow_date,
            'due_date' => $borrowing->due_date,
          ];
        }),
      'recent_purchases' => BookPurchase::with(['book', 'member.user'])
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($purchase) {
          return [
            'id' => $purchase->id,
            'type' => 'purchase',
            'book_title' => $purchase->book->title,
            'member_name' => $purchase->member->name,
            'quantity' => $purchase->quantity,
            'amount' => $purchase->total_amount,
            'date' => $purchase->purchase_date,
          ];
        }),
      'recent_users' => User::with('userable')
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($user) {
          return [
            'id' => $user->id,
            'type' => 'user_registration',
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            'date' => $user->created_at,
          ];
        }),
    ];
  }

  /**
   * Get chart data
   *
   * @return array
   */
  protected function getChartData(): array
  {
    return [
      'borrowings_per_month' => $this->getBorrowingsPerMonth(),
      'purchases_per_month' => $this->getPurchasesPerMonth(),
      'revenue_per_month' => $this->getRevenuePerMonth(),
      'users_per_month' => $this->getUsersPerMonth(),
      'popular_categories' => $this->getPopularCategories(),
      'popular_books' => $this->getPopularBooks(),
    ];
  }

  /**
   * Get borrowings per month for the last 12 months
   *
   * @return array
   */
  protected function getBorrowingsPerMonth(): array
  {
    $data = Borrowing::select(
      DB::raw('DATE_FORMAT(borrow_date, "%Y-%m") as month'),
      DB::raw('count(*) as count')
    )
      ->where('borrow_date', '>=', now()->subMonths(12))
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    return $data->mapWithKeys(function ($item) {
      return [$item->month => $item->count];
    })->toArray();
  }

  /**
   * Get purchases per month for the last 12 months
   *
   * @return array
   */
  protected function getPurchasesPerMonth(): array
  {
    $data = BookPurchase::select(
      DB::raw('DATE_FORMAT(purchase_date, "%Y-%m") as month'),
      DB::raw('count(*) as count')
    )
      ->where('purchase_date', '>=', now()->subMonths(12))
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    return $data->mapWithKeys(function ($item) {
      return [$item->month => $item->count];
    })->toArray();
  }

  /**
   * Get revenue per month for the last 12 months
   *
   * @return array
   */
  protected function getRevenuePerMonth(): array
  {
    $data = BookPurchase::select(
      DB::raw('DATE_FORMAT(purchase_date, "%Y-%m") as month'),
      DB::raw('sum(total_amount) as revenue')
    )
      ->where('purchase_date', '>=', now()->subMonths(12))
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    return $data->mapWithKeys(function ($item) {
      return [$item->month => $item->revenue];
    })->toArray();
  }

  /**
   * Get new users per month for the last 12 months
   *
   * @return array
   */
  protected function getUsersPerMonth(): array
  {
    $data = User::select(
      DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
      DB::raw('count(*) as count')
    )
      ->where('created_at', '>=', now()->subMonths(12))
      ->groupBy('month')
      ->orderBy('month')
      ->get();

    return $data->mapWithKeys(function ($item) {
      return [$item->month => $item->count];
    })->toArray();
  }

  /**
   * Get popular categories
   *
   * @return array
   */
  protected function getPopularCategories(): array
  {
    return Category::withCount('books')
      ->orderBy('books_count', 'desc')
      ->take(10)
      ->get()
      ->map(function ($category) {
        return [
          'name' => $category->name,
          'books_count' => $category->books_count,
        ];
      })
      ->toArray();
  }

  /**
   * Get popular books (most borrowed)
   *
   * @return array
   */
  protected function getPopularBooks(): array
  {
    return Book::withCount('borrowings')
      ->orderBy('borrowings_count', 'desc')
      ->take(10)
      ->get()
      ->map(function ($book) {
        return [
          'id' => $book->id,
          'title' => $book->title,
          'borrowings_count' => $book->borrowings_count,
        ];
      })
      ->toArray();
  }

  /**
   * Get librarian-specific dashboard statistics
   *
   * @return array
   */
  public function getLibrarianStatistics(): array
  {
    return [
      'overview' => [
        'active_borrowings' => Borrowing::whereIn('status', ['borrowed', 'overdue'])->count(),
        'overdue_borrowings' => Borrowing::where('status', 'overdue')->count(),
        'due_today' => Borrowing::where('status', 'borrowed')
          ->whereDate('due_date', now())
          ->count(),
        'available_books' => Book::where('available_copies', '>', 0)->count(),
      ],
      'today' => [
        'borrowings' => Borrowing::whereDate('borrow_date', now())->count(),
        'returns' => Borrowing::whereDate('return_date', now())->count(),
        'purchases' => BookPurchase::whereDate('purchase_date', now())->count(),
      ],
      'alerts' => [
        'overdue' => Borrowing::where('status', 'overdue')->count(),
        'due_soon' => Borrowing::where('status', 'borrowed')
          ->whereBetween('due_date', [now(), now()->addDays(3)])
          ->count(),
        'low_stock' => Book::whereRaw('available_copies > 0 AND available_copies <= 5')->count(),
      ],
    ];
  }
}
