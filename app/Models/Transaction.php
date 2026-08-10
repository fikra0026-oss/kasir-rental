<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'playstation_unit_id',
        'booking_id',
        'package_id',
        'member_id',
        'customer_name',
        'payment_method',
        'subtotal',
        'discount',
        'total',
        'status',
        'payment_status',
        'started_at',
        'ended_at',
        'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(PlaystationUnit::class, 'playstation_unit_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isDeletable(): bool
    {
        return $this->created_at && $this->created_at->greaterThan(now()->subDay());
    }
}
