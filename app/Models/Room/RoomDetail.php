<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Model;

class RoomDetail extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'logo',
        'street',
        'town',
        'canton',
        'phone',
        'phonecode',
        'phonecountry',
        'website',
        'contact',
        'city',
        'zipcode',
        'activelanguages',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'activelanguages' => 'array'
    ];
}
