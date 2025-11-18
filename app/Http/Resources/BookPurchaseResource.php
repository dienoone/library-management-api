<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookPurchaseResource extends JsonResource
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
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_amount' => $this->total_amount,
            'purchase_date' => $this->purchase_date?->toDateString(),

            // Relationships
            'book' => $this->when(
                $this->relationLoaded('book'),
                function () {
                    return [
                        'id' => $this->book->id,
                        'title' => $this->book->title,
                        'isbn' => $this->book->isbn,
                        'cover_image' => $this->book->cover_image,
                        'price' => $this->book->price,
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
                    ];
                }
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
