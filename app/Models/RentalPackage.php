<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'duration_minutes',
        'description',
        'is_member',
        'is_weekend',
        'is_holiday',
    ];
}
