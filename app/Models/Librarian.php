<?php

namespace App\Models;

use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Librarian extends Model
{
    use HasFactory, HasUser;

    protected $fillable = [
        'hire_date',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];
}
