<?php

namespace App\Models\Room\Template;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
		'title',
        'room_id',
        
    ];

      /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function structure(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->HasMany(TemplateStructure::class);
    }
}
