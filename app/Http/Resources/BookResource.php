<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'publisher_name' => $this->publisher_name,
            'cover_image' => $this->cover_image,
            'publication_year' => $this->publication_year,
            'publication_date' => $this->publication_date?->toDateString(),
            'price' => $this->price,
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'borrowed_copies' => $this->total_copies - $this->available_copies,
            'can_borrow' => $this->can_borrow,
            'can_purchase' => $this->can_purchase,
            'is_available' => $this->available_copies > 0,

            // Relationships
            'authors' => $this->when(
                $this->relationLoaded('authors'),
                function () {
                    return $this->authors->map(function ($author) {
                        return [
                            'id' => $author->id,
                            'name' => $author->name,
                            'bio' => $author->bio,
                            'nationality' => $author->nationality,
                        ];
                    });
                }
            ),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'authors_count' => $this->when(
                $this->relationLoaded('authors'),
                $this->authors->count()
            ),
            'categories_count' => $this->when(
                $this->relationLoaded('categories'),
                $this->categories->count()
            ),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
