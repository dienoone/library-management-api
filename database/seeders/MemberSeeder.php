<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        // Create 40 members (30 active, 5 inactive, 5 suspended)
        Member::factory()->count(30)->active()->create();
        Member::factory()->count(5)->inactive()->create();
        Member::factory()->count(5)->suspended()->create();
    }
}
