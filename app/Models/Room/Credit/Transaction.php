<?php

namespace App\Models\Room\Credit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'description',
        'amount',
        'paypalorderid',
    ];
}
