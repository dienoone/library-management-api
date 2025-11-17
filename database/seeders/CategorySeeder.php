<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction', 'description' => 'Imaginative literature and storytelling'],
            ['name' => 'Non-Fiction', 'description' => 'Fact-based literature and real events'],
            ['name' => 'Science Fiction', 'description' => 'Futuristic and scientific themes'],
            ['name' => 'Fantasy', 'description' => 'Magical and supernatural elements'],
            ['name' => 'Mystery', 'description' => 'Crime and detective stories'],
            ['name' => 'Romance', 'description' => 'Love and relationship stories'],
            ['name' => 'Thriller', 'description' => 'Suspenseful and exciting stories'],
            ['name' => 'Biography', 'description' => 'Life stories of real people'],
            ['name' => 'History', 'description' => 'Historical events and periods'],
            ['name' => 'Science', 'description' => 'Scientific topics and discoveries'],
            ['name' => 'Technology', 'description' => 'Technical and computer-related topics'],
            ['name' => 'Art', 'description' => 'Visual arts and design'],
            ['name' => 'Cookbooks', 'description' => 'Recipes and cooking techniques'],
            ['name' => 'Travel', 'description' => 'Travel guides and experiences'],
            ['name' => 'Children', 'description' => 'Books for young readers'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
