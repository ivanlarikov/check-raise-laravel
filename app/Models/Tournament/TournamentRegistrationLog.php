<?php
namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class TournamentRegistrationLog extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'status_from',
        'status_to',
		'position',
		'added_by'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
