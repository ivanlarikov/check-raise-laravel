<?php

namespace App\Observers\Tournament;

use App\Models\Tournament\Tournament;

class TournamentObserver
{
    public $afterCommit = true;
    /**
     * Handle the Tournament "created" event.
     */
    public function created(Tournament $tournament): void
    {
        //
        //die('kp');
    }

    public function updating(Tournament $tournament): void
    {
        //die('kp');
    }
    /**
     * Handle the Tournament "updated" event.
     */
    public function updated(Tournament $tournament): void
    {
        //die('kp');
    }

    /**
     * Handle the Tournament "deleted" event.
     */
    public function deleted(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "restored" event.
     */
    public function restored(Tournament $tournament): void
    {
        //
    }

    /**
     * Handle the Tournament "force deleted" event.
     */
    public function forceDeleted(Tournament $tournament): void
    {
        //
    }
}
