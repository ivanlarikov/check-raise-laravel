<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type'=>$this->type,
            'status'=>$this->status,
            'title'=>$this->title,
            'slug'=>$this->slug,
            'content'=>$this->content,
            'variables'=>$this->variables,
        ];
    }
}
