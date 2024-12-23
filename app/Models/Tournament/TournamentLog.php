<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class TournamentLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tournament_id',
        'room_id',
        'type',
        'user_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function tournament(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(Tournament::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
