<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Database\Seeder;

class BorrowingSeeder extends Seeder
{
    public function run(): void
    {
        $activeMembers = Member::where('status', 'active')->get();
        $books = Book::where('can_borrow', true)->get();

        foreach ($activeMembers as $member) {
            // Each active member borrows 0-4 books
            $borrowCount = rand(0, 4);

            for ($i = 0; $i < $borrowCount; $i++) {
                $book = $books->random();

                // Check if book is available and member can borrow
                if ($book->isAvailable() && $member->canBorrow()) {
                    $borrowing = Borrowing::factory()->create([
                        'book_id' => $book->id,
                        'member_id' => $member->id,
                    ]);

                    // Update available copies
                    if ($borrowing->status === 'borrowed') {
                        $book->decrement('available_copies');
                    }
                }
            }
        }

        // Create some overdue borrowings
        $overdueCount = 10;
        for ($i = 0; $i < $overdueCount; $i++) {
            $book = $books->random();
            $member = $activeMembers->random();

            if ($book->isAvailable() && $member->canBorrow()) {
                $borrowing = Borrowing::factory()->overdue()->create([
                    'book_id' => $book->id,
                    'member_id' => $member->id,
                ]);

                $book->decrement('available_copies');
            }
        }
    }
}
