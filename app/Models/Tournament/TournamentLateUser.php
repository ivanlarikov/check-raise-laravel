<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TournamentLateUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'user_id',
        'latetime'
    ];
}
