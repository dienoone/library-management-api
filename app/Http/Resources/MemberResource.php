<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'membership_date' => $this->membership_date?->toDateString(),
            'status' => $this->status,
            'max_borrow_limit' => $this->max_borrow_limit,
            'active_borrowings_count' => $this->whenLoaded('borrowings', function () {
                return $this->getActiveBorrowingsCount();
            }),
            'can_borrow' => $this->whenLoaded('borrowings', function () {
                return $this->canBorrow();
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'borrowings' => BorrowingResource::collection($this->whenLoaded('borrowings')),
            'purchases' => BookPurchaseResource::collection($this->whenLoaded('purchases')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
