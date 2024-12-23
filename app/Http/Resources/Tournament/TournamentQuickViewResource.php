<?php

namespace App\Http\Resources\Tournament;

use App\Http\Resources\Tournament\User\TournamentPlayerResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentQuickViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
           'id'=>$this->id,
           'startingstack'=>$this->detail->startingstack,
           'level_duration'=>$this->detail->level_duration,
           'reservedplayers'=>$this->detail->reservedplayers,
           'maxplayers'=>$this->detail->maxplayers,
           'players'=>[
                'registered'=> TournamentPlayerResourceCollection::make($this->registeredPlayers),
                'waiting'=>TournamentPlayerResourceCollection::make($this->waitingPlayers),
           ],
           'structure'=>$this->structure,
           'room_manager_id' => $this->room->user_id
        ];
    }
}
