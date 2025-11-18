<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
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
            'borrow_date' => $this->borrow_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'status' => $this->status,
            'renewal_count' => $this->renewal_count,
            'notes' => $this->notes,

            // Calculated fields
            'is_overdue' => $this->status === 'borrowed' && $this->due_date && $this->due_date->isPast(),
            'days_overdue' => $this->when(
                $this->status === 'borrowed' && $this->due_date && $this->due_date->isPast(),
                $this->due_date->diffInDays(now())
            ),
            'days_remaining' => $this->when(
                $this->status === 'borrowed' && $this->due_date && $this->due_date->isFuture(),
                now()->diffInDays($this->due_date)
            ),

            // Relationships
            'book' => $this->when(
                $this->relationLoaded('book'),
                function () {
                    return [
                        'id' => $this->book->id,
                        'title' => $this->book->title,
                        'isbn' => $this->book->isbn,
                        'cover_image' => $this->book->cover_image,
                    ];
                }
            ),
            'member' => $this->when(
                $this->relationLoaded('member'),
                function () {
                    return [
                        'id' => $this->member->id,
                        'name' => $this->member->name,
                        'email' => $this->member->email,
                        'status' => $this->member->status,
                    ];
                }
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
