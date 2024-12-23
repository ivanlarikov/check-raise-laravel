<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomStatisticsTournamentResourceCollection extends ResourceCollection
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
      'data' => $this->collection,
      'roomTitle' => $request->user()->room->title,
    ];
  }
}
