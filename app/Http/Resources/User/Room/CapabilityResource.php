<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Resources\Json\JsonResource;

class CapabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return ['cap'=>$this->capability];
    }
}
