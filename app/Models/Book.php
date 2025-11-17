<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'publisher_name',
        'cover_image',
        'total_copies',
        'available_copies',
        'price',
        'publication_year',
        'publication_date',
        'can_borrow',
        'can_purchase',
    ];

    protected $casts = [
        'can_borrow' => 'boolean',
        'can_purchase' => 'boolean',
        'price' => 'decimal:2',
        'publication_date' => 'date',
    ];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(BookPurchase::class);
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0 && $this->can_borrow;
    }

    public function canBePurchased(): bool
    {
        return $this->can_purchase && $this->price > 0;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
            return $this->cover_image;
        }

        return asset('storage/' . $this->cover_image);
    }
}
