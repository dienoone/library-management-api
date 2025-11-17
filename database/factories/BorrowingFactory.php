<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowingFactory extends Factory
{
    public function definition(): array
    {
        $borrowDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $dueDate = clone $borrowDate;
        $dueDate->modify('+14 days'); // 2 weeks borrowing period

        $status = $this->faker->randomElement(['borrowed', 'returned']);

        return [
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'return_date' => $status === 'returned' ? $this->faker->dateTimeBetween($borrowDate, $dueDate) : null,
            'status' => $status,
            'renewal_count' => $this->faker->numberBetween(0, 2),
            'notes' => $this->faker->optional()->sentence(),
            'book_id' => Book::factory(),
            'member_id' => Member::factory(),
        ];
    }

    // State methods for different statuses
    public function borrowed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'borrowed',
            'return_date' => null,
        ]);
    }

    public function returned(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'returned',
            'return_date' => $this->faker->dateTimeBetween($attributes['borrow_date'], $attributes['due_date']),
        ]);
    }

    public function overdue(): static
    {
        $borrowDate = $this->faker->dateTimeBetween('-60 days', '-31 days');
        $dueDate = clone $borrowDate;
        $dueDate->modify('+14 days');

        return $this->state(fn(array $attributes) => [
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'status' => 'borrowed',
            'return_date' => null,
        ]);
    }
}
