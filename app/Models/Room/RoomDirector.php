<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Model;

class RoomDirector extends Model
{
    protected $fillable = [
        'room_id',
        'user_id',
    ];
}
