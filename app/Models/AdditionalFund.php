<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'description',
        'amount',
        'date',
    ];
}
