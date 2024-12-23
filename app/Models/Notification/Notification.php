<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'status',
        'title',
        'slug',
        'content',
        'variables',
    ];

    protected $casts = [
        'status' => 'boolean',
        'title' => 'array',
        'content' => 'array',
        'variables' => 'array',
    ];
}
