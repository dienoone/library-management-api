<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'membership_date',
        'status',
        'max_borrow_limit',
    ];

    protected $casts = [
        'membership_date' => 'date',
    ];

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(BookPurchase::class);
    }

    public function canBorrow(): bool
    {
        return $this->status === 'active' &&
            $this->borrowings()->where('status', 'borrowed')->count() < $this->max_borrow_limit;
    }

    public function getActiveBorrowingsCount(): int
    {
        return $this->borrowings()->where('status', 'borrowed')->count();
    }
}
