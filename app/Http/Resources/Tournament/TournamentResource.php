<?php

namespace App\Http\Resources\Tournament;
use App\Models\Room\RoomUser;
use App\Models\Room\RoomSetting;
use App\Models\Tournament\TournamentLateUser;
use App\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      $userId = $request->user() ? $request->user()->id : $request->userId;
      $isSuspend = 0;

      if ($userId) {
        $lateArrival = TournamentLateUser::where('tournament_id', $this->id)->where('user_id', $userId)->first();
        if ($lateArrival) {
          $late = substr($lateArrival->latetime, 0, -3);
        } else {
          $late = '';
        }

        $roomUser = $this->room->room_users()->where('user_id', $userId)->first();

        if(!empty($roomUser)) {
          $isSuspend = $roomUser->pivot->is_suspend;
        }

        $user = User::find($userId);
        if($user->status === 2) {
          $isSuspend = 1;
        }
      } else {
        $late = '';
      }
      if ($this->room) {
        $room_id = $this->room->id;
        $roomSetting = RoomSetting::where('room_id', $room_id)->first();
        if ($roomSetting) {
          $is_late_arrival = $roomSetting->is_late_arrival;
        } else {
          $is_late_arrival = 0;
        }
      } else {
        $is_late_arrival = 0;
      }
      return [
        'id' => $this->id,
        'title' => $this->title,
        'slug' => $this->slug,
        'startday' => $this->startday,
        'detail' => $this->detail,
        'room' => $this->room,
        'archived' => $this->archived,
        'status' => $this->status,
        'closed' => $this->closed,
        'reentry' => $this->rebuycount->count(),
        'cumulated' => $this->getRakesum(),
        'players' => [
          'registered' => $this->registeredPlayers->count(),
          'waiting' => $this->waitingPlayers->count(),
          'checkin' => $this->checkinPlayers->count(),
        ],
        'isuser' => (empty($userId)) ? false : $this->isRegistered($userId),
        'iswaiting' => (empty($userId)) ? false : $this->isWaiting($userId),
        'late' => $late,
        'is_late_arrival' => $is_late_arrival,
        'disable_registration' => $this->disable_registration,
        'is_suspend' => $isSuspend,
      ];
    }
}
