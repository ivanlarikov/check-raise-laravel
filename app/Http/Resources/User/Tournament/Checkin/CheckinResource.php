<?php

namespace App\Http\Resources\User\Tournament\Checkin;

use App\Http\Resources\Tournament\User\TournamentCheckInPlayerResourceCollection;
use App\Http\Resources\Tournament\Log\TournamentRegistrationLogCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Room\RoomSetting;

class CheckinResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    $room_id = $this->room_id;
    $roomSetting = RoomSetting::where('room_id', $room_id)->first();
    if ($roomSetting) {
      $is_late_arrival = $roomSetting->is_late_arrival;
      $is_membership = $roomSetting->is_membership;
      $is_bonus = $roomSetting->is_bonus;
    } else {
      $is_late_arrival = 0;
      $is_membership = 0;
      $is_bonus = 0;
    }
    return [
      'detail' => $this->detail,
      'status' => $this->status,
      'slug' => $this->slug,
      'closed' => $this->closed,
      'archived' => $this->archived,
      'is_freeze' => $this->is_freeze,
      'title' => $this->title,
      'reentries' => $this->rebuycount->sum('rebuycount'),
      'checkins' => $this->checkinPlayers->count(),
      'registered' => TournamentCheckInPlayerResourceCollection::make(
        $this->registeredPlayers
      ),
      'waiting' => TournamentCheckInPlayerResourceCollection::make(
        $this->waitingPlayers
      ),
      'log' => TournamentRegistrationLogCollection::make(
        $this->registration_log
      ),
      'is_late_arrival' => $is_late_arrival,
      'is_membership' => $is_membership,
      'is_bonus' => $is_bonus,
      'room' => $this->room
    ];
  }
}
