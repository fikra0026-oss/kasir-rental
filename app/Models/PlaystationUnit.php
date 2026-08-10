<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaystationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'tv',
        'status',
        'hourly_rate',
        'photo_path',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
