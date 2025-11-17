<?php

namespace Database\Factories;

use App\Authorization\AuthorizationRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bio' => $this->faker->paragraphs(3, true),
            'nationality' => $this->faker->country(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($author) {
            // Create a user for the author and link it
            $user = User::factory()->create([
                'userable_id' => $author->id,
                'userable_type' => \App\Models\Author::class,
            ]);

            // Assign author role
            $authorRole = Role::where('name', AuthorizationRole::AUTHOR)->first();
            if ($authorRole) {
                $user->roles()->attach($authorRole);
            }
        });
    }
}
