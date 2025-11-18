<?php

namespace App\Services;

use App\Authorization\AuthorizationRole;
use App\Models\User;
use App\Models\Member;
use App\Models\Author;
use App\Models\Librarian;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthService
{
  public function register(array $data, string $type)
  {
    return DB::transaction(function () use ($data, $type) {
      $profile = $this->createProfile($type, $data);

      $user = User::create([
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'phone' => $data['phone'] ?? null,
        'address' => $data['address'] ?? null,
        'birth_date' => $data['birth_date'] ?? null,
      ]);

      $user->userable()->associate($profile);

      $this->assignRole($user, $type);

      $user->save();

      $token = $user->createToken('auth_token')->plainTextToken;


      return [
        'user' => $user->fresh()->load(['userable', 'roles']),
        'token' => $token
      ];
    });
  }

  protected function createProfile(string $type, array $data)
  {
    switch ($type) {
      case AuthorizationRole::MEMBER:
        return Member::create([
          'membership_date' => now(),
          'status' => 'active',
          'max_borrow_limit' => $data['max_borrow_limit'] ?? 5, // Default limit
        ]);

      case AuthorizationRole::AUTHOR:
        return Author::create([
          'bio' => $data['bio'] ?? null,
          'nationality' => $data['nationality'] ?? null,
        ]);

      case AuthorizationRole::LIBRARIAN:
        return Librarian::create([
          'hire_date' => $data['hire_date'] ?? now(),
        ]);

      default:
        throw new BadRequestException("Invalid user type: {$type}");
    }
  }

  protected function assignRole(User $user, string $roleName): void
  {

    if (!$roleName) {
      throw new BadRequestException("No role mapping for type: {$roleName}");
    }

    $role = Role::where('name', $roleName)->first();

    if (!$role) {
      throw new BadRequestException("Role '{$roleName}' not found in database");
    }

    $user->assignRole($role->name);
  }

  public function login(array $data): array
  {
    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
      throw new BadRequestException('Invalid credentials');
    }

    $token = $user->createToken('auth-token')->plainTextToken;

    return [
      'user' => $user->load(['userable', 'roles']),
      'token' => $token,
    ];
  }

  public function logout(): void
  {
    $user = Auth::user();

    $token = $user->tokens()->where('id', $user->currentAccessToken()->id)->first();

    if ($token) {
      $token->delete();
    }
  }
}
