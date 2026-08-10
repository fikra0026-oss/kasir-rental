<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'points',
        'total_transactions',
        'level',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
