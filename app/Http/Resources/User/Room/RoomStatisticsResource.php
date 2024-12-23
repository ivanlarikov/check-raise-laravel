<?php

namespace App\Http\Resources\User\Room;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomStatisticsResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    $registeredPlayers = $this->tournaments->sum('registered_players_count');
    $checkinPlayers = $this->tournaments->sum('checkin_players_count');
    $reEntries = $this->tournaments->sum('reentry_sum');
    $cumulatedPrizePools = 0;
    $cumulatedRakes = 0;

    foreach ($this->tournaments as $tournament) {
      $detail = $tournament->detail;
      $checkinPlayersCount = $tournament->checkin_players_count;
      $reentrySum = $tournament->reentry_sum;

//    prizePool = (tournament.buyin * checkedInPlayers) + (tournament.reentry * reEntries);
      $prizePool = ($detail->buyin * $checkinPlayersCount) + ($detail->reentry * $reentrySum);
//    bounties = (tournament.bounty * checkedInPlayers) + ( tournament.reentry_bounty * reEntries);
      $bounties = ($detail->bounty * $checkinPlayersCount) + ($detail->reentry_bounty * $reentrySum);
      $cumulatedPrizePools += $prizePool + $bounties;

//    rakes = tournament.rake * checkedInPlayers + reEntries * (tournament.reentriesrake || 0);
      $cumulatedRakes += $detail->rake * $checkinPlayersCount + ($reentrySum * $detail->reentriesrake);
    }

    return [
      'id' => $this->id,
      'title' => $this->title,
      'tournaments' => $this->tournaments->count(),
      'registered_players' => $registeredPlayers,
      'players_without_check_in' => $registeredPlayers - $checkinPlayers,
      'players_with_check_in' => $checkinPlayers,
      're_entries' => $reEntries,
      'cumulated_prize_pools' => $cumulatedPrizePools,
      'cumulated_rakes' => $cumulatedRakes,
    ];
  }
}
