<?php
namespace App\Http\Resources\Room;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'total' => $this->collection->count(),
            'data' => $this->collection
        ];
    }
}
