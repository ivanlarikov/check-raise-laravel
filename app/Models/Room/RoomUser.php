<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Model;

class RoomUser extends Model
{

  protected $casts = [
    'is_suspend' => 'boolean',
    'id_checked' => 'boolean',
  ];

  protected $fillable = [
    'room_id',
    'user_id',
    'is_suspend',
    'id_checked'
  ];
}
