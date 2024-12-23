<?php

namespace App\Http\Resources\Common\Dashboard;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardResourceCollection extends ResourceCollection
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
