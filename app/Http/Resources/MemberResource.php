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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'membership_date' => $this->membership_date?->toDateString(),
            'is_active' => $this->is_active,

            // Only include borrowing count if loaded
            'active_borrowings_count' => $this->when(
                $this->relationLoaded('borrowings'),
                function () {
                    return $this->borrowings->where('status', 'borrowed')->count();
                }
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
