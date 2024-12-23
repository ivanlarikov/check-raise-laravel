<?php

namespace App\Models\Room\Template;

use Illuminate\Database\Eloquent\Model;

class TemplateStructure extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order',
        'sb',
        'bb',
        'ante',
        'duration',
        'isbreak',
        'breaktitle',
        'template_id',
    ];
}
