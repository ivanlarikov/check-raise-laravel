<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

class TournamentStructure extends Model
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
        'tournament_id',
    ];
}
