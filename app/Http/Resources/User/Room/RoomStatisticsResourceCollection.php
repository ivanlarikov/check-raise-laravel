<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomStatisticsResourceCollection extends ResourceCollection
{
  /**
   * Transform the resource collection into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    return [
      'total' => $this->collection->count(),
      'data' => $this->collection
    ];
  }
}
