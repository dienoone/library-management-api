<?php

namespace Database\Factories;

use App\Authorization\AuthorizationRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'membership_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']),
            'max_borrow_limit' => $this->faker->numberBetween(3, 8),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($member) {
            // Create a user for the member and link it
            $user = User::factory()->create([
                'userable_id' => $member->id,
                'userable_type' => \App\Models\Member::class,
            ]);

            // Assign member role
            $memberRole = Role::where('name', AuthorizationRole::MEMBER)->first();
            if ($memberRole) {
                $user->roles()->attach($memberRole);
            }
        });
    }

    // State methods for different statuses
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
