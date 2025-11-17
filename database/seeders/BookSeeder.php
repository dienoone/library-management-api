<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // Create books
        $books = Book::factory()->count(100)->create();

        $authors = Author::all();
        $categories = Category::all();

        // Attach random authors and categories to each book
        $books->each(function ($book) use ($authors, $categories) {
            // Attach 1-3 random authors
            $book->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Attach 1-2 random categories
            $book->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );
        });
    }
}
