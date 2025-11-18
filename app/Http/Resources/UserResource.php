<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'birth_date' => $this->birth_date?->toDateString(),
            'email_verified_at' => $this->email_verified_at,

            // Author specific fields
            'bio' => $this->when(
                $this->isAuthor() && $this->userable,
                fn() => $this->userable->bio
            ),
            'nationality' => $this->when(
                $this->isAuthor() && $this->userable,
                fn() => $this->userable->nationality
            ),
            'books_count' => $this->when(
                $this->isAuthor() && $this->userable,
                fn() => $this->userable->relationLoaded('books') ? $this->userable->books->count() : 0
            ),

            // Member specific fields
            'membership_date' => $this->when(
                $this->isMember() && $this->userable,
                fn() => $this->userable->membership_date?->toDateString()
            ),
            'status' => $this->when(
                $this->isMember() && $this->userable,
                fn() => $this->userable->status
            ),
            'max_borrow_limit' => $this->when(
                $this->isMember() && $this->userable,
                fn() => $this->userable->max_borrow_limit
            ),
            'active_borrowings_count' => $this->when(
                $this->isMember() && $this->userable,
                fn() => $this->userable->getActiveBorrowingsCount()
            ),
            'can_borrow' => $this->when(
                $this->isMember() && $this->userable,
                fn() => $this->userable->canBorrow()
            ),

            // Librarian specific fields
            'hire_date' => $this->when(
                $this->isLibrarian() && $this->userable,
                fn() => $this->userable->hire_date?->toDateString()
            ),
            'years_of_service' => $this->when(
                $this->isLibrarian() && $this->userable && $this->userable->hire_date,
                fn() => $this->userable->hire_date->diffInYears(now())
            ),

            // Roles and permissions
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
