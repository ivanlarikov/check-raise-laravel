<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Resources\Json\JsonResource;

class DirectorResource extends JsonResource
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
            'name'=>$this->username,
            'email'=>$this->email,
            'capabilities'=>CapabilityResourceCollection::make(
                        $this->directory_capabilities
                    )
        ];
    }
}
