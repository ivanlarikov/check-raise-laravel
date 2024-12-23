<?php

namespace App\Http\Resources\Tournament\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Player list for tournament quick view.
 */
class TournamentPlayerResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'email'     => $this->email,
      'firstname' => $this->profile ? $this->profile->firstname : '',
      'lastname'  =>  $this->profile ? $this->profile->lastname : '',
      'nickname'  =>  $this->profile ? $this->profile->nickname : '',
      'displayoption' => $this->profile ? $this->profile->displayoption : '',
      'created'       => $this->profile ? $this->pivot->created_at : '',
    ];
  }
}
