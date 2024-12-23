<?php
namespace App\Http\Resources\User\Room\Credit;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
		
        $room_id=$request->user()->room->id;
		$tr=\App\Models\Room\Room::find($room_id);
        return [
            'total' => $this->collection->count(),
            'balance'=>$tr->credits,
            'data' => $this->collection
        ];;
    }
}
