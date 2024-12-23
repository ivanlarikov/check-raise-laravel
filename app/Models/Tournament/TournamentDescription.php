<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

class TournamentDescription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language',
        'description',
        'tournament_id',
    ];
}
