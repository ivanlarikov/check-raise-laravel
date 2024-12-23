<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Model;

class RoomDescription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language',
        'description',
        // 'room_id',
    ];
}
