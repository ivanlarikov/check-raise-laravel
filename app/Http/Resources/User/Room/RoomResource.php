<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'detail'=>$this->detail,
            'description'=>$this->description,
			'expiry'=>$this->expiry,
			'credits'=>$this->credits,
            'status'=>$this->status,
            'buyuinlimit'=>$this->buyuinlimit,
            'maxnumberoftournament'=>$this->maxnumberoftournament,
            'maxnumberofpremium'=>$this->maxnumberofpremium,
            'latearrivaldelay'=>$this->latearrivaldelay,
            'activetournaments'=>$this->when( ( empty($request->user()) || !$request->user()->hasAnyRole(['Room Manager']) ), function () {
                return $this->getActivetournaments();
            }),
        ];
    }
}
