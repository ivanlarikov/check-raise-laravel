<?php

namespace App\Http\Resources\Tournament;

use App\Http\Resources\Tournament\User\TournamentUserResourceCollection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Room\RoomSetting;

class TournamentDetailResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
//    $privatePlayers = 0;
//    foreach ($this->waitingPlayers as $player) {
//      if ($player->profile->displayoption == "private")
//        $privatePlayers++;
//    }
    if ($this->detail->bounusdeadline) {
      $bonus_reg_date = $this->detail->bounusdeadline;
    } else if ($this->detail->startday) {
      $roomSetting = RoomSetting::where('room_id', $this->room->id)->first();

      if (!empty($roomSetting)) {
        $current_status = $roomSetting->current_bonus_status;
        if ($current_status == 1) {
          $hrs = $roomSetting->number_of_hours;
          $bonus_reg_date = date('Y-m-d H:i', strtotime($this->detail->startday . " -$hrs hours"));
        } else if ($current_status == 2) {
          $days = $roomSetting->number_of_day;
          $bonus_reg_date = date('Y-m-d H:i', strtotime($this->detail->startday . " -$days days"));
        } else if ($current_status == 3) {
          $weekday = $roomSetting->fix_weekday;
          if ($weekday == 1) {
            $last = "Monday";
          } else if ($weekday == 2) {
            $last = "Tuesday";
          } else if ($weekday == 3) {
            $last = "Wednesday";
          } else if ($weekday == 4) {
            $last = "Thursday";
          } else if ($weekday == 5) {
            $last = "Friday";
          } else if ($weekday == 6) {
            $last = "Saturday";
          } else {
            $last = "Sunday";
          }
          $bonus_reg_date = date('Y-m-d H:i', strtotime($this->detail->startday . " last $last"));
        } else {
          $bonus_reg_date = null;
        }

      } else {
        $bonus_reg_date = null;
      }
    } else {
      $bonus_reg_date = null;
    }

    $isSuspend = 0;
    if (!empty($request->user()->id)) {
      $roomUser = $this->room->room_users()->where('user_id', $request->user()->id)->first();
      if (!empty($roomUser)) {
        $isSuspend = $roomUser->pivot->is_suspend;
      }
      if ($request->user()->status === 2) {
        $isSuspend = 1;
      }
    }

    return [
      'id' => $this->id,
      'title' => $this->title,
      'slug' => $this->slug,
//      'startday' => $this->startday,
      'startingstack' => $this->detail->startingstack,
      'level_duration' => $this->detail->level_duration,
      'reservedplayers' => $this->detail->reservedplayers,
      'maxplayers' => $this->detail->maxplayers,
      'detail' => $this->detail,
      'room' => $this->room,
      'isuser' => (empty($request->user()->id)) ? false : $this->isRegistered($request->user()->id),
      'iswaiting' => (empty($request->user()->id)) ? false : $this->isWaiting($request->user()->id),
//      'privateplayers' => $privatePlayers,
      'players' => [
        'registered' => $this->getPlayers($this->registeredPlayers),
        'waiting' => $this->getPlayers($this->waitingPlayers),
      ],
      'structure' => $this->structure,
//      'description' => $this->description,
      'description' => [],
      'bonus_reg_date' => $bonus_reg_date,
      'status' => $this->status,
      'disable_registration' => $this->disable_registration,
      'is_suspend' => $isSuspend,
    ];
  }

  /**
   * @param $players - Registered and waiting players.
   * @return array
   */
  private function getPlayers($players): array
  {
    $total = count($players);
    $data = [];
    foreach ($players as $player) {
      $data[] = [
        'id' => $player->id,
        'firstname' => $player->profile->firstname,
        'lastname' => $player->profile->lastname,
        'nickname' => $player->profile->nickname,
        'displayoption' => $player->profile->displayoption,
      ];
    }

    return ['total' => $total, 'data' => $data];
  }
}
