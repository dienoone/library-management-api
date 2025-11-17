<?php

namespace Database\Factories;

use App\Authorization\AuthorizationRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LibrarianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($librarian) {
            // Create a user for the librarian and link it
            $user = User::factory()->create([
                'userable_id' => $librarian->id,
                'userable_type' => \App\Models\Librarian::class,
            ]);

            // Assign librarian role
            $librarianRole = Role::where('name', AuthorizationRole::LIBRARIAN)->first();
            if ($librarianRole) {
                $user->roles()->attach($librarianRole);
            }
        });
    }
}
