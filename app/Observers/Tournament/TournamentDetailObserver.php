<?php

namespace App\Observers\Tournament;

use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\TournamentLog;
use Illuminate\Support\Facades\Auth;

class TournamentDetailObserver
{
    /**
     * Handle the TournamentDetail "created" event.
     */
    public function created(TournamentDetail $tournamentDetail): void
    {
        if(empty(Auth::id()))
            return;
        TournamentLog::create([
            'tournament_id'=>$tournamentDetail->tournament_id,
            'room_id' => $tournamentDetail->tournament->room_id,
            'user_id'=>Auth::id(),
            'type'=>1, //update
            'changes'=>[]
        ]);
    }

    /**
     * Handle the TournamentDetail "updated" event.
     */
    public function updated(TournamentDetail $tournamentDetail): void
    {
        if(empty(Auth::id()))
            return;
        //die;
        $changes=[];
        if($tournamentDetail->isDirty('type')){
            // email has changed
            $changes['type']=[
                'from' => $tournamentDetail->getOriginal('type'),
                'to' => $tournamentDetail->type,
            ];
        }
        if($tournamentDetail->isDirty('isshorthanded')){
            // email has changed
            $changes['isshorthanded']=[
                'from' => $tournamentDetail->getOriginal('isshorthanded'),
                'to' => $tournamentDetail->isshorthanded,
            ];
        }
        if($tournamentDetail->isDirty('dealertype')){
            // email has changed
            $changes['dealertype']=[
                'from' => $tournamentDetail->getOriginal('dealertype'),
                'to' => $tournamentDetail->dealertype,
            ];
        }
        if($tournamentDetail->isDirty('buyin')){
            // email has changed
            $changes['buyin']=[
                'from' => $tournamentDetail->getOriginal('buyin'),
                'to' => $tournamentDetail->buyin,
            ];
        }
        if($tournamentDetail->isDirty('bounty')){
            // email has changed
            $changes['bounty']=[
                'from' => $tournamentDetail->getOriginal('bounty'),
                'to' => $tournamentDetail->bounty,
            ];
        }
        if($tournamentDetail->isDirty('rake')){
            // email has changed
            $changes['rake']=[
                'from' => $tournamentDetail->getOriginal('rake'),
                'to' => $tournamentDetail->rake,
            ];
        }
        if($tournamentDetail->isDirty('maxreentries')){
            // email has changed
            $changes['maxreentries']=[
                'from' => $tournamentDetail->getOriginal('maxreentries'),
                'to' => $tournamentDetail->maxreentries,
            ];
        }
        if($tournamentDetail->isDirty('startday')){
            // email has changed
            $changes['startday']=[
                'from' => $tournamentDetail->getOriginal('startday'),
                'to' => $tournamentDetail->startday,
            ];
        }
        if($tournamentDetail->isDirty('lastday')){
            // email has changed
            $changes['lastday']=[
                'from' => $tournamentDetail->getOriginal('lastday'),
                'to' => $tournamentDetail->lastday,
            ];
        }
        if($tournamentDetail->isDirty('startingstack')){
            // email has changed
            $changes['startingstack']=[
                'from' => $tournamentDetail->getOriginal('startingstack'),
                'to' => $tournamentDetail->startingstack,
            ];
        }
        if($tournamentDetail->isDirty('level_duration')){
            // email has changed
            $changes['level_duration']=[
                'from' => $tournamentDetail->getOriginal('level_duration'),
                'to' => $tournamentDetail->level_duration,
            ];
        }
        if($tournamentDetail->isDirty('maxplayers')){
            // email has changed
            $changes['maxplayers']=[
                'from' => $tournamentDetail->getOriginal('maxplayers'),
                'to' => $tournamentDetail->maxplayers,
            ];
        }
        if($tournamentDetail->isDirty('reservedplayers')){
            // email has changed
            $changes['reservedplayers']=[
                'from' => $tournamentDetail->getOriginal('reservedplayers'),
                'to' => $tournamentDetail->reservedplayers,
            ];
        }
        if($tournamentDetail->isDirty('lateregformat')){
            // email has changed
            $changes['lateregformat']=[
                'from' => $tournamentDetail->getOriginal('lateregformat'),
                'to' => $tournamentDetail->lateregformat,
            ];
        }
        if(!empty($changes)){
            TournamentLog::create([
                'tournament_id'=>$tournamentDetail->tournament_id,
                'room_id' => $tournamentDetail->tournament->room_id,
                'user_id'=>Auth::id(),
                'type'=>2, //update
                'changes'=>$changes
            ]);
        }
        //
        //die('kp');
    }

    /**
     * Handle the TournamentDetail "deleted" event.
     */
    public function deleted(TournamentDetail $tournamentDetail): void
    {
        //
    }

    /**
     * Handle the TournamentDetail "restored" event.
     */
    public function restored(TournamentDetail $tournamentDetail): void
    {
        //
    }

    /**
     * Handle the TournamentDetail "force deleted" event.
     */
    public function forceDeleted(TournamentDetail $tournamentDetail): void
    {
        //
    }
}
