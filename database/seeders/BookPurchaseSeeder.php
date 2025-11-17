<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookPurchase;
use App\Models\Member;
use Illuminate\Database\Seeder;

class BookPurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();
        $purchasableBooks = Book::where('can_purchase', true)->get();

        foreach ($members as $member) {
            // Each member makes 0-3 purchases
            $purchaseCount = rand(0, 3);

            for ($i = 0; $i < $purchaseCount; $i++) {
                $book = $purchasableBooks->random();

                BookPurchase::factory()->create([
                    'book_id' => $book->id,
                    'member_id' => $member->id,
                    'unit_price' => $book->price,
                ]);
            }
        }
    }
}
