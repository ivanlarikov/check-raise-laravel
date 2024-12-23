<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomStatisticsTournamentResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    $detail = $this->detail;
    $registered = $this->registeredPlayers->count();
    $checkin = $this->checkinPlayers->count();
//    $reentries = $this->rebuycount->count();
    $reentries = $this->rebuycount->sum('rebuycount');
    $prizePool = (($detail->buyin + $detail->bounty) * $checkin) + (($detail->reentry + $detail->reentry_bounty) * $reentries);

    return [
      'id' => $this->id,
      'title' => $this->title,
      'startday' => $detail->startday,
      'buyin' => $detail->buyin,
      'bounty' => $detail->bounty,
      'reentry' => $detail->reentry,
      'reentry_bounty' => $detail->reentry_bounty,
      'players' => [
        'registered' => $registered,
        'checkin' => $checkin,
        'checkin_percentage' => $this->calculatePercentage($registered, $checkin),
        'without_checkin' => $registered - $checkin,
        'without_checkin_percentage' => $this->calculatePercentage($registered, $registered - $checkin),
      ],
      'reentries' => $reentries,
      'reentries_percentage' => $this->calculatePercentage($checkin, $reentries),
      'prize_pool' => $prizePool,
      'rake' => $detail->rake,
      'reentries_rake' => $detail->reentriesrake,
      'total_rakes' => $checkin * $detail->rake + $reentries * $detail->reentriesrake,
    ];
  }

  private function calculatePercentage($total, $numerator): int|string
  {
    $percentage = 0;

    if ($total > 0 && $numerator > 0) {
      $percentage = number_format((($numerator / $total) * 100), 2);
    }

    return $percentage;
  }
}
