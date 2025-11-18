<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class MemberService
{
  public function getAllMembersPaginate(array $filters = [])
  {
    $query = Member::query();

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->whereHas('user', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // Apply status filter
    if (!empty($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    // Apply date filters
    if (!empty($filters['start_date'])) {
      $query->whereDate('membership_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
      $query->whereDate('membership_date', '<=', $filters['end_date']);
    }

    // Apply sorting
    $orderBy = $filters['order_by'] ?? 'membership_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    // Handle sorting by user fields
    if (in_array($orderBy, ['first_name', 'last_name', 'email'])) {
      $query->join('users', function ($join) {
        $join->on('members.id', '=', 'users.userable_id')
          ->where('users.userable_type', Member::class);
      })->orderBy("users.{$orderBy}", $orderDirection)
        ->select('members.*');
    } else {
      $query->orderBy($orderBy, $orderDirection);
    }

    // Load relationships if requested
    if (
      isset($filters['with_borrowings']) &&
      filter_var($filters['with_borrowings'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->with('borrowings');
    }

    if (
      isset($filters['with_purchases']) &&
      filter_var($filters['with_purchases'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->with('purchases');
    }

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
    return Member::with('user')
      ->orderBy('membership_date', 'desc')
      ->get();
  }

  public function getMember(int $id)
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    return $member->load('user', 'borrowings', 'purchases');
  }

  public function search(string $search)
  {
    return Member::whereHas('user', function ($query) use ($search) {
      $query->where('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%");
    })
      ->with('user')
      ->orderBy('membership_date', 'desc')
      ->get();
  }

  public function updateMember(int $id, array $data)
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    return DB::transaction(function () use ($member, $data) {
      // Update member data
      $member->update([
        'membership_date' => $data['membership_date'] ?? $member->membership_date,
        'status' => $data['status'] ?? $member->status,
        'max_borrow_limit' => $data['max_borrow_limit'] ?? $member->max_borrow_limit,
      ]);

      // Update user data
      if ($member->user) {
        $member->user->update([
          'first_name' => $data['first_name'] ?? $member->user->first_name,
          'last_name' => $data['last_name'] ?? $member->user->last_name,
          'phone' => $data['phone'] ?? $member->user->phone,
          'address' => $data['address'] ?? $member->user->address,
          'birth_date' => $data['birth_date'] ?? $member->user->birth_date,
        ]);
      }

      return $member->load('user', 'borrowings');
    });
  }

  public function deleteMember(int $id)
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    throw_if(
      $member->borrowings()->where('status', 'borrowed')->exists(),
      ConflictException::class,
      'Cannot delete member with active borrowings. Please return all borrowed books first.'
    );

    // Delete associated user and member
    DB::transaction(function () use ($member) {
      if ($member->user) {
        $member->user->delete();
      }
      $member->delete();
    });
  }

  public function getMemberBorrowings(int $id)
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    return $member->borrowings()->with('book')->get();
  }

  public function getActiveBorrowingsCount(int $id): int
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    return $member->getActiveBorrowingsCount();
  }

  public function canBorrow(int $id): bool
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    return $member->canBorrow();
  }

  public function updateStatus(int $id, string $status)
  {
    $member = Member::find($id);
    throw_if(!$member, NotFoundException::class, 'Member not found');

    $member->update([
      'status' => $status
    ]);

    return $member->load('user');
  }
}
