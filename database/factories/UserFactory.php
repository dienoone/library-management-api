<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            // userable_id and userable_type will be null for regular users
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State for users with no specific role (regular users)
     */
    public function regular(): static
    {
        return $this->state(fn(array $attributes) => [
            'userable_id' => null,
            'userable_type' => null,
        ]);
    }

    /**
     * State for admin users (without specific userable)
     */
    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $adminRole = \App\Models\Role::where('name', \App\Authorization\AuthorizationRole::ADMIN)->first();
            if ($adminRole) {
                $user->roles()->attach($adminRole);
            }
        });
    }

    /**
     * State for young users
     */
    public function young(): static
    {
        return $this->state(fn(array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-25 years', '-18 years'),
        ]);
    }

    /**
     * State for senior users
     */
    public function senior(): static
    {
        return $this->state(fn(array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-80 years', '-60 years'),
        ]);
    }
}
