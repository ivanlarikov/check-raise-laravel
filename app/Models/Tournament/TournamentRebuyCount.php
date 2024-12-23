<?php
namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class TournamentRebuyCount extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'rebuycount',
        'status_to'
    ];

    
}
