<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone',
        'date',
        'start_time',
        'duration_minutes',
        'playstation_unit_id',
        'package_id',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(PlaystationUnit::class, 'playstation_unit_id');
    }

    public function package()
    {
        return $this->belongsTo(RentalPackage::class, 'package_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
