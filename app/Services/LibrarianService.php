<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Librarian;
use Illuminate\Support\Facades\DB;

class LibrarianService
{
  public function getAllLibrariansPaginate(array $filters = [])
  {
    $query = Librarian::query();

    // apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->whereHas('user', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // apply sorting filter
    $orderBy = $filters['order_by'] ?? 'first_name';
    $orderDirection = $filters['order_direction'] ?? 'asc';

    if (in_array($orderBy, ['first_name', 'last_name', 'email'])) {
      $query->join('users', 'librarians.id', '=', 'users.userable_id')
        ->where('users.userable_type', Librarian::class)
        ->orderBy("users.{$orderBy}", $orderDirection);
    } else {
      $query->orderBy($orderBy, $orderDirection);
    }

    // load user relationship if requested
    if (
      isset($filters['with_user']) &&
      filter_var($filters['with_user'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->with('user');
    }

    $perPage = $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return Librarian::with('user')->orderBy('created_at', 'asc')->get();
  }

  public function getLibrarian(int $id)
  {
    $librarian = Librarian::with('user')->find($id);
    throw_if(!$librarian, NotFoundException::class, 'Librarian not found');

    return $librarian;
  }

  public function search(string $search)
  {
    return Librarian::whereHas('user', function ($query) use ($search) {
      $query->where('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%");
    })->with('user')->orderBy('created_at', 'asc')->get();
  }

  public function updateLibrarian(int $id, array $data)
  {
    $librarian = Librarian::with('user')->find($id);
    throw_if(!$librarian, NotFoundException::class, 'Librarian not found');

    return DB::transaction(function () use ($librarian, $data) {
      // Update librarian data
      if (isset($data['hire_date'])) {
        $librarian->update([
          'hire_date' => $data['hire_date'] ?? $librarian->hire_date
        ]);
      }

      // Update user data
      if ($librarian->user) {
        $librarian->user->update([
          'first_name' => $data['first_name'] ?? $librarian->user->first_name,
          'last_name' => $data['last_name'] ?? $librarian->user->last_name,
          'email' => $data['email'] ?? $librarian->user->email,
          'phone' => $data['phone'] ?? $librarian->user->phone,
          'address' => $data['address'] ?? $librarian->user->address,
          'birth_date' => $data['birth_date'] ?? $librarian->user->birth_date,
        ]);
      }

      return $librarian->load('user');
    });
  }

  public function deleteLibrarian(int $id)
  {
    $librarian = Librarian::with('user')->find($id);
    throw_if(!$librarian, NotFoundException::class, 'Librarian not found');

    return DB::transaction(function () use ($librarian) {
      // Delete user record
      if ($librarian->user) {
        $librarian->user->delete();
      }

      // Delete librarian record
      $librarian->delete();
    });
  }
}
