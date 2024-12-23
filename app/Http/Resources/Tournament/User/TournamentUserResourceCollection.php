<?php

namespace App\Http\Resources\Tournament\User;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TournamentUserResourceCollection extends ResourceCollection
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
            'data' => $this->collection->sortBy('pivot.created_at')->values()->all(),
        ];
    }
}
