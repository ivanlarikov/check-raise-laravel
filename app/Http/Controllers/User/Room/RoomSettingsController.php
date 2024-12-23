<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Services\Tournament\TournamentService;
use App\Http\Resources\Room\RoomStatisticsResourceCollection;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Requests\User\Room\RoomCreateRequest;
use App\Http\Requests\User\Room\RoomUpdateRequest;
use App\Http\Resources\User\Room\RoomStatisticsResource;
use App\Models\Room\RoomDescription as RoomRoomDescription;
use App\Models\Room\Room;
use App\Models\Room\RoomDetail as RoomRoomDetail;
use App\Models\Room\RoomSetting;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class RoomSettingsController extends Controller
{
	use ResponseTrait;
	/**
	 * @var RoomService
	 */
	protected RoomService $room;
	protected TournamentService $tournament;

	/**
	 * @param RoomService $tournment
	 */
	public function __construct(RoomService $room, TournamentService $tournament)
	{
		$this->room = $room;
		$this->tournament = $tournament;
	}

	public function index(Request $request)
	{
		if (isset($request->user()->room->id)) {
			$room_id = $request->user()->room->id;
			$roomsetting = RoomSetting::where('room_id', $room_id)->first();
			if ($roomsetting) {
				$response = array(
					'status' => true,
					'data' => $roomsetting
				);
				return json_encode($response);
			}
		}
		$response = array(
			'status' => true,
			'data' => []
		);
		return json_encode($response);
	}
	/**
	 * @param RoomStatisticsResource $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		$roomId = $request->user()->room->id;
		// $roomSetting = RoomSetting::where('room_id', $roomId)->first();

		// if (empty($roomSetting)) {
		// 	$roomSetting = new RoomSetting();
		// }

		RoomSetting::updateOrCreate(
			['room_id' => $roomId],
			[
				'is_membership' => $request->is_membership,
				'is_late_arrival' => $request->is_late_arrival,
				'is_bonus' => $request->is_bonus,
				'current_bonus_status' => $request->current_bonus_status,
				'number_of_hours' => $request->number_of_hours,
				'number_of_day' => $request->number_of_day,
				'fix_weekday' => $request->fix_weekday,
				'day_time' => $request->day_time,
				'weekday_time' => $request->weekday_time,
			]
		);

		$this->jsonResponseSuccess([
			'message' => trans('user/room/room.update.success')
		]);
	}
}
