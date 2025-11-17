<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategorySeeder::class,
            AuthorSeeder::class,
            BookSeeder::class,
            MemberSeeder::class,
            LibrarianSeeder::class,
            BorrowingSeeder::class,
            BookPurchaseSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
