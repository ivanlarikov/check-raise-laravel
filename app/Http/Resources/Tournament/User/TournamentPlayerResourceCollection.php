<?php

namespace App\Http\Resources\Tournament\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TournamentPlayerResourceCollection extends ResourceCollection
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
      'data' => $this->collection->sortBy('pivot.created_at')->values()->all(),
    ];
  }
}
