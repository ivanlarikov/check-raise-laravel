<?php
namespace App\Http\Resources\User\Tournament\Checkin;

use App\Http\Resources\Tournament\User\TournamentUserResourceCollection;
use App\Http\Resources\Tournament\Log\TournamentRegistrationLogCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckinResource extends JsonResource
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
            'detail'=>$this->detail,
            'status'=>$this->status,
            'slug'=>$this->slug,
            'closed'=>$this->closed,
            'archived'=>$this->archived,
            'title'=>$this->title,
            'reentries'=>$this->rebuycount->sum('rebuycount'),
            'checkins'=>$this->checkinPlayers->count(),
            'registered'=>TournamentUserResourceCollection::make(
                $this->registeredPlayers
            ),
            'waiting'=>TournamentUserResourceCollection::make(
                $this->waitingPlayers
            ),
            'log'=>TournamentRegistrationLogCollection::make(
                $this->registration_log
            )
        ];
    }
}
