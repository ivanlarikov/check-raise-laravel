<?php

namespace App\Http\Resources\Tournament;

use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title'=>$this->title,
            'slug'=>$this->slug,
            'startday'=>$this->startday,
            'detail'=>$this->detail,
            'room'=>$this->room,
            'status'=>$this->status,
            'slug'=>$this->slug,
            'closed'=>$this->closed,
            'players'=>[
                'registered'=>$this->registeredPlayers->count(),
                'waiting'=>$this->waitingPlayers->count(),
                'checkin'=>$this->checkinPlayers->count(),
            ],
            'isuser'=>(empty($request->user()->id)) ? false : $this->isRegistered($request->user()->id),
            'iswaiting'=>(empty($request->user()->id)) ? false : $this->isWaiting($request->user()->id)
        ];
    }
}
