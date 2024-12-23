<?php

namespace App\Http\Resources\Tournament\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Room\RoomUser;
use App\Models\Room\RoomMember;
use App\Models\Tournament\Tournament;
use Auth;

class TournamentUserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		//echo $this->pivot->room_id;
		//die;
		//echo $this->id.'<br/>';
		//echo $this->pivot_room_id;
		//echo "<pre>";print_r($this);echo "</pre>";
		//die;
		if (!empty($this->pivot->tournament_id)) {

			$tournament = Tournament::find($this->pivot->tournament_id);
			$room_id = $tournament->room_id;
			if (!empty($room_id)) {

				/** Check For Suspend **/

				$suspend_data = RoomUser::select('is_suspend', 'created_at', 'id_checked')->where('room_id', $room_id)->where('user_id', $this->id)->first();
				if ($suspend_data) {
					$issuspend = $suspend_data['is_suspend'];
				} else {
					$issuspend = '';
				}

				/** Check For Member **/
				$ismemebership = RoomMember::where('room_id', $room_id)->where('user_id', $this->id)->first();
				if ($ismemebership) {
					$membership = $ismemebership->expiry;
					$room_member_id = $ismemebership->id;
				} else {
					$membership = "-";
					$room_member_id = 0;
				}
			} else {
				$issuspend = '';
				$membership = "-";
				$room_member_id = 0;
				$room_id = 0;
			}
		} else if (!empty($this->pivot->room_id)) {
			$room_id = $this->pivot->room_id;
			if (!empty($room_id)) {

				/** Check For Suspend **/

				$suspend_data = RoomUser::select('is_suspend', 'created_at', 'id_checked')->where('room_id', $room_id)->where('user_id', $this->id)->first();
				if ($suspend_data) {
					$issuspend = $suspend_data['is_suspend'];
				} else {
					$issuspend = '';
				}

				/** Check For Member **/
				$ismemebership = RoomMember::where('room_id', $room_id)->where('user_id', $this->id)->first();
				if ($ismemebership) {
					$membership = $ismemebership->expiry;
					$room_member_id = $ismemebership->id;
				} else {
					$membership = "-";
					$room_member_id = 0;
				}
			} else {
				$issuspend = '';
				$membership = "-";
				$room_member_id = 0;
				$room_id = 0;
			}
		} else {
			$issuspend = '';
			$membership = "-";
			$room_member_id = 0;
			$room_id = 0;
		}


		return [
			'id' => $this->id,
			'is_suspend' => $issuspend,
			'email'     => $this->email,
			'firstname' => $this->profile ? $this->profile->firstname : '',
			'lastname'  =>  $this->profile ? $this->profile->lastname : '',
			'dob'       =>  $this->profile ? $this->profile->dob : '',
			'street'    =>  $this->profile ? $this->profile->street : '',
			'language'  =>  $this->profile ? $this->profile->language : '',
			'nickname'  =>  $this->profile ? $this->profile->nickname : '',
			'city'      =>  $this->profile ? $this->profile->city ? $this->profile->city : '-' : '',
			'zipcode'   =>  $this->profile ? $this->profile->zipcode ? $this->profile->zipcode : '-' : '',
			'displayoption' => $this->profile ? $this->profile->displayoption : '',
			'phonecode'     => $this->profile ? $this->profile->phonecode : '',
			'phonecountry'  => $this->profile ? $this->profile->phonecountry : '',
			'phonenumber'   => $this->profile ? $this->profile->phonenumber : '',
			'created'       => $this->profile ? $this->pivot->created_at : '',
			'with' => $this->checkinPlayers->count(),
			'without' => $this->checkinPlayers->count(),
			'late' => $this->last_register($this->pivot->tournament_id, $this->id),
			'membership' => $membership,
			'room_member_id' => $room_member_id,
			'room_id' => $room_id,
			'rakes' => ($this->getUserrebuycount($this->pivot->tournament_id)) ? $this->getUserrebuycount($this->pivot->tournament_id) : 0,
			'ischeckedin'   => $this->isCheckedin($this->pivot->tournament_id),
			'reentries'     => ($this->getUserrebuycount($this->pivot->tournament_id)) ? $this->getUserrebuycount($this->pivot->tournament_id) : 0,
			'tournaments'     => ($this->getTournaments($this->pivot->tournament_id)) ? $this->getTournaments($this->pivot->tournament_id) : '',
			'lateannouncement' => $this->lateannouncement($this->pivot->tournament_id, $this->id),
			'first_registration_date' => empty($suspend_data->created_at) ? '' : $suspend_data->created_at,
			'id_checked' => empty($suspend_data->id_checked) ? false : $suspend_data->id_checked,
		];
	}
}
