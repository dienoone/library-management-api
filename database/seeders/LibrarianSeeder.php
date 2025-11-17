<?php

namespace Database\Seeders;

use App\Models\Librarian;
use Illuminate\Database\Seeder;

class LibrarianSeeder extends Seeder
{
    public function run(): void
    {
        Librarian::factory()->count(8)->create();
    }
}
