<?php

namespace App\Http\Resources\Tournament;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentTableResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    $user = $request->user;

    $isSuspend = 0;
    $late = '';

    if (isset($user)) {
      $lateArrival = $this->latePlayers()->where('user_id', $user->id)->first();
      if ($lateArrival) {
        $late = substr($lateArrival->latetime, 0, -3);
      }

      if ($user->status === 2) {
        $isSuspend = 1;
      } else {
        $roomUser = $this->room->room_users()->where('user_id', $user->id)->first();
        if (!empty($roomUser)) {
          $isSuspend = $roomUser->pivot->is_suspend;
        }
      }
    }

    return [
      'id' => $this->id,
      'title' => $this->title,
      'slug' => $this->slug,
      'detail' => $this->detail,
      'room' => $this->room,
      'archived' => $this->archived,
      'status' => $this->status,
      'closed' => $this->closed,
      'reentry' => $this->rebuycount_count,
      'cumulated' => $this->getRakesum(),
      'players' => [
        'registered' => $this->registered_players_count,
        'waiting' => $this->waiting_players_count,
        'checkin' => $this->checkin_players_count,
      ],
      'isuser' => empty($user) ? false : $this->isRegistered($user->id),
      'iswaiting' => empty($user) ? false : $this->isWaiting($user->id),
      'late' => $late,
      'is_late_arrival' => $this->room->setting->is_late_arrival ?? 0,
      'disable_registration' => $this->disable_registration,
      'is_suspend' => $isSuspend,
    ];
  }
}
