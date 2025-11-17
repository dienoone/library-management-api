<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $publicationYear = $this->faker->numberBetween(1900, 2024);
        $publishers = [
            'Penguin Random House',
            'HarperCollins',
            'Simon & Schuster',
            'Hachette Book Group',
            'Macmillan Publishers',
            'Oxford University Press',
            'Cambridge University Press',
            'Springer Nature',
            'Elsevier',
            'Wiley',
            'Pearson Education',
            'McGraw-Hill Education',
            'Cengage Learning',
            'Bloomsbury Publishing',
            'Scholastic Corporation'
        ];

        return [
            'title' => $this->faker->sentence(4),
            'isbn' => $this->faker->isbn13(),
            'description' => $this->faker->paragraphs(3, true),
            'publisher_name' => $this->faker->randomElement($publishers),
            'cover_image' => $this->faker->optional(0.7)->imageUrl(300, 450, 'book', true), // 70% chance to have cover
            'total_copies' => $this->faker->numberBetween(1, 10),
            'available_copies' => function (array $attributes) {
                return $attributes['total_copies'];
            },
            'price' => $this->faker->randomFloat(2, 5, 50),
            'publication_year' => $publicationYear,
            'publication_date' => $this->faker->dateTimeBetween("$publicationYear-01-01", "$publicationYear-12-31"),
            'can_borrow' => $this->faker->boolean(80), // 80% can be borrowed
            'can_purchase' => $this->faker->boolean(30), // 30% can be purchased
        ];
    }

    // State methods for specific scenarios
    public function withCover(): static
    {
        return $this->state(fn(array $attributes) => [
            'cover_image' => $this->faker->imageUrl(300, 450, 'book', true),
        ]);
    }

    public function withoutCover(): static
    {
        return $this->state(fn(array $attributes) => [
            'cover_image' => null,
        ]);
    }

    public function purchasable(): static
    {
        return $this->state(fn(array $attributes) => [
            'can_purchase' => true,
            'price' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    public function notPurchasable(): static
    {
        return $this->state(fn(array $attributes) => [
            'can_purchase' => false,
            'price' => null,
        ]);
    }
}
