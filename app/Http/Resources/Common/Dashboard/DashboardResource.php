<?php

namespace App\Http\Resources\Common\Dashboard;
use App\Http\Resources\User\Room\RoomResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
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
            'room'=>$this->getroom(),
            'detail'=>$this->detail,
            'description'=>$this->description,
    
        ];
    }
}
