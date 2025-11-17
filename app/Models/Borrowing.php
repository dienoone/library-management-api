<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_date',
        'due_date',
        'return_date',
        'status',
        'renewal_count',
        'notes',
        'book_id',
        'member_id',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'borrowed' && $this->due_date < now();
    }

    public function canRenew(): bool
    {
        return $this->status === 'borrowed' &&
            $this->renewal_count < 3 && // Max 3 renewals
            !$this->isOverdue();
    }
}
