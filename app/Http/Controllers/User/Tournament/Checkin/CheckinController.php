<?php

namespace App\Http\Controllers\User\Tournament\Checkin;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;
use App\Services\Room\RoomService;
use App\Services\User\UserService;

use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Resources\User\UserResourceCollection;

use App\Http\Requests\User\Tournament\Checkin\RegisterPlayerRequest;
use App\Http\Requests\User\Tournament\Checkin\CheckinCountRequest;
use App\Http\Requests\User\Tournament\Checkin\GetPlayerRequest;
use App\Http\Resources\User\Tournament\Checkin\CheckinResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use App\Models\Room\RoomMember;
use App\Models\Room\RoomUser;
use Illuminate\Http\Request;
use Mail;

class CheckinController extends Controller
{
  use ResponseTrait;

  /**
   * @var TournamentService
   */
  protected TournamentService $tournment;
  protected RoomService $room;
  protected UserService $user;

  /**
   * @param TournamentService $tournment
   */
  public function __construct(TournamentService $tournment, RoomService $room, UserService $user)
  {
    $this->tournment = $tournment;
    $this->room = $room;
    $this->user = $user;
  }

  public function index($id)
  {
    /*$data = array('name'=>"Virat Gandhi");
Mail::send('emails.admin.newmanager', $data, function($message) {
  $message->to('abc@gmail.com', 'Tutorials Point')->subject('Laravel Basic Testing Mail');
  $message->from('xyz@gmail.com','Virat Gandhi');
});*/
    return CheckinResource::make(
      $this->tournment->show(
        ['id' => $id]
      )
    );
  }

  public function register(Request $request)
  {
    $this->tournment->registerPlayer($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function deregister(Request $request)
  {
    // TODO: update logic in Tournament Service.
    $this->tournment->deregisterWaitlist($request->id, $request->user_id);
    $this->tournment->deregisterPlayer($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  /*public function deregisterfromwaiting(Request $request)
  {
      $this->tournment->deregisterfromwaiting($request->id,$request->user_id);
      return $this->jsonResponseSuccess(
          trans('admin/room/room.create.success')
      );

  }*/

  public function updateIdCheck(Request $request)
  {
    RoomUser::updateOrCreate(
      ['room_id' => $request->room_id, 'user_id' => $request->user_id],
      ['id_checked' => 1],
    );
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function users(GetPlayerRequest $request): UserResourceCollection
  {
    $data = $request->validated();
    // get users
    return UserResourceCollection::make(
      $this->tournment->getPlayers($data['tournamentId'], $data['keyword'])
    );
  }

  public function checkin(Request $request)
  {
    $this->tournment->checkinPlayer($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function cancelcheckin(Request $request)
  {
    $this->tournment->cancelcheckinPlayer($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  //plusrebuy
  public function plusrebuy(Request $request)
  {
    $this->tournment->plusrebuy($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  //minusrebuy
  public function minusrebuy(Request $request)
  {
    $this->tournment->minusrebuy($request->id, $request->user_id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function updateCounts(CheckinCountRequest $request): bool|\Illuminate\Http\JsonResponse|string
  {
    $data = $request->validated();
    if ($data['reservedplayers'] > $data['maxplayers']) {
      $data = array(
        'status' => false,
        'msg' => 'Max number of player not less than reserved players'
      );
      return json_encode($data);
    }
    $this->tournment->updateCounts($data['tournament_id'], $data['maxplayers'], $data['reservedplayers']);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function checkout(Request $request)
  {
    $this->tournment->checkout($request->id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
  }

  public function latearraival(Request $request)
  {
    if (empty($request->latetime)) {
      return $this->jsonResponseFail(
        trans('tournament/latearraival.create.fail')
      );
    }
    $id = $request->tournament_id;
    $palyer_id = $request->palyer_id;
    $touranment = $this->tournment->show(
      ['id' => $id]
    );

    if (empty($touranment)) {
      return $this->jsonResponseFail(
        trans('tournament/latearraival.create.fail')
      );
    }

    //check if the user is registered or not.
    /*if( !$touranment->isRegistered($palyer_id))
    {
        return $this->jsonResponseFail(
            trans('tournament/latearraival.create.notregistered')
        );
    }*/
    //
    if (!$touranment->isLate($palyer_id)) {
      $touranment->latePlayers()->create(
        [
          "user_id" => $palyer_id,
          "latetime" => $request->latetime
        ]
      );
      $touranment->registration_log()->create([
        'user_id' => $palyer_id,
        'status_from' => 0,
        'status_to' => 4,
        'added_by' => Auth::user()->roles->pluck('name')[0]
      ]);
      return $this->jsonResponseSuccess(
        trans('tournament/latearraival.create.success')
      );
    } else {
      $touranment->latePlayers()->where(['user_id' => $palyer_id])->update(
        ['latetime' => $request->latetime]
      );
      $touranment->registration_log()->create([
        'user_id' => $palyer_id,
        'status_from' => 0,
        'status_to' => 5,
        'added_by' => Auth::user()->roles->pluck('name')[0]

      ]);
      return $this->jsonResponseSuccess(
        trans('tournament/latearraival.update.success')
      );
    }
  }

  public function latearrivalremove($id, $playerid)
  {
    $touranment = $this->tournment->show(
      ['id' => $id]
    );
    if (empty($touranment)) {
      return $this->jsonResponseFail(
        trans('tournament/latearraival.delete.fail')
      );
    }
    $touranment->latePlayers()->where(['user_id' => $playerid])->delete();
    $touranment->registration_log()->create([
      'user_id' => $playerid,
      'status_from' => 0,
      'status_to' => 6,
      'added_by' => Auth::user()->roles->pluck('name')[0]
    ]);
    return $this->jsonResponseSuccess(
      trans('tournament/latearraival.delete.success')
    );
  }

  public function updateexpiry(Request $request)
  {
    if ($request->id != 0) {
      $room = RoomMember::find($request->id);
      $room->expiry = $request->expiry;
      $room->save();
      $data = array(
        'status' => true,
        'message' => 'Membership Expiry Date Updated!!!'
      );
    } else {
      $room = new RoomMember;
      $room->room_id = $request->room_id;
      $room->user_id = $request->user_id;
      $room->expiry = $request->expiry;
      $room->save();
      $data = array(
        'status' => true,
        'message' => 'Memership Added!!!'
      );
    }
    return json_encode($data);
  }
}
