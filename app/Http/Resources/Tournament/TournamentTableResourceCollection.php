<?php

namespace App\Http\Resources\Tournament;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TournamentTableResourceCollection extends ResourceCollection
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
        ->sortBy('detail.startday')
        ->groupBy('detail.startday')
        ->flatMap(function ($group) {
          return $group->shuffle();
        })
        ->toArray(),
    ];
  }
}
