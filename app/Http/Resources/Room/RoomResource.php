<?php

namespace App\Http\Resources\Room;

use App\Models\Room\RoomSetting;
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
        if (isset($this->manager->profile->firstname)) {
            $firstname = $this->manager->profile->firstname;
        } else {
            $firstname = '';
        }
        if (isset($this->manager->profile->lastname)) {
            $lastname = $this->manager->profile->lastname;
        } else {
            $lastname = '';
        }
        $roomsetting = RoomSetting::where('room_id', $this->id)->first();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'manager' => [
                'firstname' => $firstname,
                'lastname' => $lastname,
            ],
            'manager_id' => $this->user_id,
            'expiry' => $this->expiry,
            'credits' => $this->credits,
            'buyuinlimit' => $this->buyuinlimit,
            'buy_in_limit_without_reentry' => $this->buy_in_limit_without_reentry,
            'maxnumberoftopbanner' => $this->maxnumberoftopbanner,
            'maxnumberofbottombanner' => $this->maxnumberofbottombanner,
            'maxnumberoftournament' => $this->maxnumberoftournament,
            'maxnumberofpremium' => $this->maxnumberofpremium,
            'latearrivaldelay' => $this->latearrivaldelay,
            'status' => $this->status,
            'detail' => $this->detail,
            'description' => $this->description,
            'room_setting' => $roomsetting
        ];
    }
}
