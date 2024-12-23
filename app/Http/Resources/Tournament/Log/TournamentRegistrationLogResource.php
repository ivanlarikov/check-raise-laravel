<?php

namespace App\Http\Resources\Tournament\Log;

use Illuminate\Http\Resources\Json\JsonResource;

class TournamentRegistrationLogResource extends JsonResource
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
            'player'=>$this->user->profile->firstname.' '.$this->user->profile->lastname,
            'action'=>$this->status_to,
            'datetime'=>$this->created_at,
			'position'=>$this->position,
			'added_by'=>$this->added_by,
        ];
    }
}
