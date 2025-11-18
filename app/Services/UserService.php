<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Author;
use App\Models\Librarian;
use App\Models\Member;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
  /**
   * Get paginated list of users
   *
   * @param array $filters
   * @return LengthAwarePaginator
   */
  public function getAllPaginated(array $filters = []): LengthAwarePaginator
  {
    $query = User::query()->with(['userable', 'roles']);

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('phone', 'like', "%{$search}%");
      });
    }

    // Filter by user type
    if (!empty($filters['user_type'])) {
      $userType = $filters['user_type'];
      $query->where('userable_type', 'App\\Models\\' . ucfirst($userType));
    }

    // Filter by role
    if (!empty($filters['role'])) {
      $query->whereHas('roles', function ($q) use ($filters) {
        $q->where('name', $filters['role']);
      });
    }

    // Filter by email verification
    if (isset($filters['email_verified'])) {
      $emailVerified = filter_var($filters['email_verified'], FILTER_VALIDATE_BOOLEAN);
      if ($emailVerified) {
        $query->whereNotNull('email_verified_at');
      } else {
        $query->whereNull('email_verified_at');
      }
    }

    // Filter by date range
    if (!empty($filters['start_date'])) {
      $query->whereDate('created_at', '>=', $filters['start_date']);
    }
    if (!empty($filters['end_date'])) {
      $query->whereDate('created_at', '<=', $filters['end_date']);
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'created_at';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    $allowedOrderColumns = ['first_name', 'last_name', 'email', 'created_at'];
    if (in_array($orderBy, $allowedOrderColumns)) {
      $query->orderBy($orderBy, $orderDirection);
    } else {
      $query->orderBy('created_at', 'desc');
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  /**
   * Get all users without pagination
   *
   * @return Collection
   */
  public function getAll(): Collection
  {
    return User::with(['userable', 'roles'])->orderBy('created_at', 'desc')->get();
  }

  /**
   * Get a single user by ID
   *
   * @param int $id
   * @return User
   */
  public function getById(int $id): User
  {
    $user = User::with(['userable', 'roles.rolePermissions'])->find($id);
    throw_if(!$user, NotFoundException::class, 'User not found');

    return $user;
  }

  /**
   * Update user
   *
   * @param int $id
   * @param array $data
   * @return User
   */
  public function update(int $id, array $data): User
  {
    $user = $this->getById($id);

    return DB::transaction(function () use ($user, $data) {
      // Update user attributes
      $user->update([
        'first_name' => $data['first_name'] ?? $user->first_name,
        'last_name' => $data['last_name'] ?? $user->last_name,
        'email' => $data['email'] ?? $user->email,
        'phone' => $data['phone'] ?? $user->phone,
        'address' => $data['address'] ?? $user->address,
        'birth_date' => $data['birth_date'] ?? $user->birth_date,
      ]);

      // Update password if provided
      if (!empty($data['password'])) {
        $user->update(['password' => Hash::make($data['password'])]);
      }

      // Update profile-specific data
      if ($user->userable) {
        $this->updateProfile($user, $data);
      }

      // Update roles if provided
      if (isset($data['role_ids'])) {
        $user->roles()->sync($data['role_ids']);
      }

      return $user->fresh()->load(['userable', 'roles.rolePermissions']);
    });
  }

  /**
   * Update user profile based on type
   *
   * @param User $user
   * @param array $data
   * @return void
   */
  protected function updateProfile(User $user, array $data): void
  {
    if ($user->isMember() && isset($data['member_data'])) {
      $user->userable->update([
        'status' => $data['member_data']['status'] ?? $user->userable->status,
        'max_borrow_limit' => $data['member_data']['max_borrow_limit'] ?? $user->userable->max_borrow_limit,
      ]);
    } elseif ($user->isAuthor() && isset($data['author_data'])) {
      $user->userable->update([
        'bio' => $data['author_data']['bio'] ?? $user->userable->bio,
        'nationality' => $data['author_data']['nationality'] ?? $user->userable->nationality,
      ]);
    } elseif ($user->isLibrarian() && isset($data['librarian_data'])) {
      $user->userable->update([
        'hire_date' => $data['librarian_data']['hire_date'] ?? $user->userable->hire_date,
      ]);
    }
  }

  /**
   * Delete user
   *
   * @param int $id
   * @return void
   */
  public function delete(int $id): void
  {
    $user = $this->getById($id);

    // Check for associated records
    if ($user->isMember()) {
      $member = $user->userable;
      throw_if(
        $member->borrowings()->exists() || $member->purchases()->exists(),
        ConflictException::class,
        'Cannot delete member with associated borrowings or purchases'
      );
    } elseif ($user->isAuthor()) {
      $author = $user->userable;
      throw_if(
        $author->books()->exists(),
        ConflictException::class,
        'Cannot delete author with associated books'
      );
    }

    DB::transaction(function () use ($user) {
      // Detach roles
      $user->roles()->detach();

      // Delete profile
      if ($user->userable) {
        $user->userable->delete();
      }

      // Delete user
      $user->delete();
    });
  }

  /**
   * Search users
   *
   * @param string $search
   * @return Collection
   */
  public function search(string $search): Collection
  {
    return User::with(['userable', 'roles'])
      ->where('first_name', 'like', "%{$search}%")
      ->orWhere('last_name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%")
      ->orWhere('phone', 'like', "%{$search}%")
      ->orderBy('first_name')
      ->get();
  }

  /**
   * Assign roles to user
   *
   * @param int $id
   * @param array $roleIds
   * @return User
   */
  public function assignRoles(int $id, array $roleIds): User
  {
    $user = $this->getById($id);
    $user->roles()->sync($roleIds);

    return $user->fresh()->load(['userable', 'roles.rolePermissions']);
  }

  /**
   * Get users by type
   *
   * @param string $type (Member, Author, Librarian)
   * @return Collection
   */
  public function getUsersByType(string $type): Collection
  {
    return User::where('userable_type', 'App\\Models\\' . ucfirst($type))
      ->with(['userable', 'roles'])
      ->get();
  }

  /**
   * Get user statistics
   *
   * @return array
   */
  public function getStatistics(): array
  {
    return [
      'total' => User::count(),
      'members' => Member::count(),
      'authors' => Author::count(),
      'librarians' => Librarian::count(),
      'verified' => User::whereNotNull('email_verified_at')->count(),
      'unverified' => User::whereNull('email_verified_at')->count(),
    ];
  }
}
