<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    use HasFactory;
    protected $fillable = [
		'key',
		'content',
		'image'
    ];

    protected $casts = [
      'content' => 'array'
    ];
}
