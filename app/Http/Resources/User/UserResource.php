<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $name = (empty($this->profile->firstname)) ? "" : $this->profile->firstname;
        $name .= (empty($this->profile->lastname)) ? "" : " " . $this->profile->lastname;

        if (!empty($this->profile->nickname)) {
            $name .= " (" . $this->profile->nickname . ")";
        }

        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $name,
            'email' => $this->email,
            'isverified' => $this->email_verified_at,
            'status' => $this->status,
        ];
    }
}
