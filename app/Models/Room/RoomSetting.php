<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'is_membership',
        'is_late_arrival',
        'is_bonus',
        'current_bonus_status',
        'number_of_hours',
        'number_of_day',
        'fix_weekday',
        'day_time',
        'weekday_time',
        'break_text',
    ];
}
