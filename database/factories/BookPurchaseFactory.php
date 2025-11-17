<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookPurchaseFactory extends Factory
{
    public function definition(): array
    {
        $unitPrice = $this->faker->randomFloat(2, 5, 50);
        $quantity = $this->faker->numberBetween(1, 3);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $unitPrice * $quantity,
            'purchase_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'book_id' => Book::factory(),
            'member_id' => Member::factory(),
        ];
    }
}
