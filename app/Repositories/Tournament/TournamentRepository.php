<?php

namespace App\Repositories\Tournament;

use App\Models\Tournament\Tournament;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class TournamentRepository extends BaseRepository
{
    /**
     * @var Tournament
     */
    protected Tournament $tournament;

    /**
     * @param Tournament $tournament
     */
    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        parent::__construct($tournament);
    }
    
}
