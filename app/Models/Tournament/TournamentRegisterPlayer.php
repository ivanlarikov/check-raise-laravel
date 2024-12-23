<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class TournamentRegisterPlayer extends Model
{
    protected $with = [
        'user',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(User::class);
    }

    

    
}
