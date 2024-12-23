<?php

namespace App\Http\Resources\Tournament\User;

use App\Models\Room\RoomMember;
use App\Models\Room\RoomUser;
use App\Models\Tournament\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Player list for Tournament CheckIn page.
 */
class TournamentCheckInPlayerResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param Request $request
   * @return array
   */
  public function toArray(Request $request): array
  {
    $tournament = Tournament::find($this->pivot->tournament_id);
    $roomId = $tournament->room_id;
    $suspendData = RoomUser::select('is_suspend', 'created_at', 'id_checked')->where('room_id', $roomId)->where('user_id', $this->id)->first();
    $roomMember = RoomMember::where('room_id', $roomId)->where('user_id', $this->id)->first();

    return [
      'id' => $this->id,
      'email' => $this->email,
      'firstname' => $this->profile ? $this->profile->firstname : '',
      'lastname' => $this->profile ? $this->profile->lastname : '',
      'nickname' => $this->profile ? $this->profile->nickname : '',
      'displayoption' => $this->profile ? $this->profile->displayoption : '',
      'phonecode' => $this->profile ? $this->profile->phonecode : '',
      'phonecountry' => $this->profile ? $this->profile->phonecountry : '',
      'phonenumber' => $this->profile ? $this->profile->phonenumber : '',
      'created' => $this->profile ? $this->pivot->created_at : '',
      'first_registration_date' => empty($suspendData->created_at) ? '' : $suspendData->created_at,
      'id_checked' => empty($suspendData->id_checked) ? false : $suspendData->id_checked,
      'ischeckedin'   => $this->isCheckedin($this->pivot->tournament_id),
      'room_id' => $roomId,
      'room_member_id' => $roomMember->id ?? null,
      'membership' => $roomMember->expiry ?? '-',
      'lateannouncement' => $this->lateannouncement($this->pivot->tournament_id, $this->id),
      'reentries'     => $this->getUserrebuycount($this->pivot->tournament_id),
      'tournaments' => $tournament->detail,
    ];
  }
}
