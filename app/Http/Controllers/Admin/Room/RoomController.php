<?php

namespace App\Http\Controllers\Admin\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Resources\Room\RoomResource;
use App\Http\Requests\User\Room\RoomCreateRequest;
use App\Http\Requests\Admin\Room\AdminRoomUpdateRequest;
use App\Http\Requests\Admin\Room\RoomStatusUpdateRequest;
use App\Models\Room\Room;
use App\Models\Room\RoomDescription;
use App\Models\Room\RoomDetail;
use App\Models\Room\RoomMember;
use App\Models\Room\RoomSetting;
use App\Models\Room\RoomUser;
use App\Models\Room\Template\Template;
use App\Models\Room\Template\TemplateStructure;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentCheckinPlayer;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\TournamentLateUser;
use App\Models\Tournament\TournamentLog;
use App\Models\Tournament\TournamentRebuyCount;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentRegistrationLog;
use App\Models\Tournament\TournamentStructure;
use App\Models\Tournament\TournamentWaitingPlayer;
use App\Models\User\User;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
  use ResponseTrait;

  /**
   * @var RoomService
   */
  protected RoomService $room;


  /**
   * @param RoomService $tournment
   */
  public function __construct(RoomService $room)

  {
    $this->room = $room;
  }

  /**
   * @param RoomResourceCollection $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function index(Request $request): RoomResourceCollection
  {

    return RoomResourceCollection::make(
      $this->room->all(null, null, null, null, null, null, null, true)
    );
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show(Request $request, $id)
  {
    $room = $this->room->show(
      ['id' => $id]
    );

    if ($room) {
      return new RoomResource(
        $room
      );
    }

    return $this->jsonResponseFail(
      trans('admin.room/show.fail')
    );
  }

  public function store(RoomCreateRequest $request): \Illuminate\Http\JsonResponse
  {
    $data = $request->validated();
    $room = $this->room->create(
      $data
    );
    if ($room) {
      $room->description()->create($data);
      $room->detail()->create($data);

      return $this->jsonResponseSuccess(
        trans('admin/room/room.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('admin/room/room.create.fail'),
      400
    );
  }

  public function update(AdminRoomUpdateRequest $request): \Illuminate\Http\JsonResponse
  {
    $data = $request->validated();
    $roomId = $data['room']['id'];
    $detail = $data['details'];
    $room = $this->room->show(['id' => $roomId]);

    $updatedCredits = $room->credits != $data['room']['credits'];
    $updatedExpiry = !(new Carbon($room->expiry))->isSameDay($data['room']['expiry']);

    $this->room->update($roomId, $data['room']);

    if ($updatedCredits) {
      sendEmail(
        'manager',
        'admin_modified_credits',
        $roomId,
        [
          'amount' => $data['room']['credits'],
          'date' => Carbon::now()->format('d.m.Y H:i')
        ]
      );
    }

    if ($updatedExpiry) {
      sendEmail(
        'manager',
        'subscription_modified',
        $roomId,
        [
          'date' => (new Carbon($data['room']['expiry']))->format('d.m.Y')
        ]
      );
    }

    /* update details */
    if (!empty($detail['logo'])) {
      if (strlen($detail['logo']) > 30) {
        $detail['logo'] = $this->uploadImage($detail['logo']);
      }
    }
    $room->detail->update($detail);

    /* update description */
    foreach ($data['descriptions'] as $key => $item) {
      $room->description()->updateOrCreate(
        ['language' => $item['language']],
        ['language' => $item['language'], 'description' => $item['description']]
      );
    }

    return $this->jsonResponseSuccess(
      trans('admin/room/room.update.success')
    );
  }

  public function destroy($id)
  {
    // $this->room->delete($id);
    $room = Room::find($id);

    RoomDescription::where('room_id', "=", $id)->delete();
    RoomDetail::where('room_id', "=", $id)->delete();
    RoomMember::where('room_id', '=', $id)->delete();
    RoomSetting::where('room_id', '=', $id)->delete();
    RoomUser::where('room_id', '=', $id)->delete();

    $templateIds = Template::where('room_id', '=', $id)->get()->pluck('id')->all();
    TemplateStructure::whereIn('template_id', $templateIds)->delete();
    Template::whereIn('id', $templateIds)->delete();

    $tournamentIds = Tournament::where('room_id', '=', $id)->get()->pluck('id')->all();
    TournamentDetail::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentDescription::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentStructure::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentRegisterPlayer::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentWaitingPlayer::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentCheckinPlayer::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentLateUser::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentRegistrationLog::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentRebuyCount::whereIn('tournament_id', $tournamentIds)->delete();
    TournamentLog::whereIn('tournament_id', $tournamentIds)->delete();
    Tournament::whereIn('id', $tournamentIds)->delete();

    User::where('id', '=', $room->user_id)->delete();
    $room->delete();

    return $this->jsonResponseSuccess(
      trans('admin/room/room.delete.success')
    );
  }

  public function status(RoomStatusUpdateRequest $request): \Illuminate\Http\JsonResponse
  {
    $data = $request->validated();
    $room = $this->room->update(
      $data['id'],
      ['status' => $data['status']]
    );

    // when de-active or suspend room, unpublish all published tournaments of the room.
    if ($data['status'] == 2 || $data['status'] == 3) {
      Tournament::where('room_id', '=', $data['id'])->where('status', '=', 1)->update(['status' => 0]);
    }

    return $this->jsonResponseSuccess(
      trans('admin/room/room.status.success')
    );
  }

  public function sendemail(request $request, $roomID)
  {
    $useremail = DB::table('room_users')
      ->select('users.email')
      ->join('users', 'users.id', '=', 'room_users.user_id')->where('room_id', "=", $roomID)->get();
    //foreach($useremails as $useremail){
    //mail($useremail->email,$request['subject'],$request['content']);
    //}
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.mail.success')
    );

    return $this->jsonResponseSuccess(
      trans('tournament/tournament.mail.fail')
    );
  }

  private function uploadImage($base64data)
  {
    $name = uniqid() . '.png';
    $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
    Storage::disk('room')->put($name, $file);
    return $name;
  }
}
