<?php

namespace App\Http\Controllers\Player\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tournament\TournamentTableResourceCollection;
use App\Services\Tournament\TournamentService;
use App\Services\User\UserService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Resources\Tournament\TournamentQuickViewResource;
use App\Http\Resources\Tournament\TournamentDetailResource;
use App\Http\Requests\User\Tournament\TournamentRegisterRequest;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Room\Room;
use App\Models\Room\RoomMember;
use App\Models\Room\RoomSetting;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
  use ResponseTrait;

  /**
   * @var TournamentService
   */
  protected TournamentService $touranment;
  protected UserService $user;

  /**
   * @param TournamentService $touranment
   */
  public function __construct(TournamentService $touranment, UserService $user)
  {
    $this->touranment = $touranment;
    $this->user = $user;
  }

  /**
   * @param Request $request
   * @return TournamentTableResourceCollection
   */
  public function index(Request $request): TournamentTableResourceCollection
  {
    //check if rooms are set
    $roomIds = $request->input('rooms');

    $query = Tournament::with('room.setting')
      ->withCount('rebuycount', 'registeredPlayers', 'waitingPlayers', 'checkinPlayers')
      ->where([
        'status' => 1,
        'closed' => 0,
        'archived' => 0
      ]);
    if (!empty($roomIds)) {
      $query = $query->whereIn('room_id', $roomIds);
    }
    $query = $query->whereHas('detail', function ($q) {
      $q->whereBetween('startday', [
        date('Y-m-d') . ' 00:00:00',
        date('Y-m-d', strtotime('+1 year')) . ' 00:00:00'
      ]);
    });
    $tournaments = $query->get();

    $request->user = $request->user();

    return TournamentTableResourceCollection::make($tournaments);
  }

  /**
   * @return JsonResponse
   */
  public function register(TournamentRegisterRequest $request): JsonResponse
  {
    if (empty(Auth::id())) {
      return $this->jsonResponseFail('Not Authenticated.');
    }

    $data = $request->validated();
    $tournament = Tournament::find($data['id']);
    $userID = Auth::id();
    $roomID = $tournament->room_id;

    if ($tournament->disable_registration) {
      return $this->jsonResponseFail('Disabled registration.');
    }

    $roomUser = $tournament->room->room_users()->where('user_id', $userID)->first();
    if (!empty($roomUser) && $roomUser->pivot->is_suspend) {
      return $this->jsonResponseFail('Suspended from this room.');
    }

    if ($request->user()->status === 2) {
      return $this->jsonResponseFail('Suspended by Admin.');
    }

    // echo $roomID;die;
    $roomSetting = RoomSetting::where('room_id', $roomID)->first();
    if ($roomSetting) {
      if ($roomSetting->is_membership != 0) {
        $member = RoomMember::where('user_id', "=", $userID)->where('room_id', "=", $roomID)->first();
        if (empty($member->room_id)) {
          $roomMember = new RoomMember;
          $roomMember->room_id = $roomID;
          $roomMember->user_id = $userID;
          $roomMember->expiry = date('Y-m-d', strtotime('+2 months'));
          $roomMember->save();
        }
      }
    }

    $this->touranment->registerPlayer($data['id'], $request->user()->id);
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
    //get max nimber of player
    // get count of all tournament registeraion
    //waiting player
    //room player
  }

  public function deregister(TournamentRegisterRequest $request)
  {
    $data = $request->validated();
    //$touranment=$this->touranment->show( ['id' => $data['id']]);
    if ($this->touranment->deregisterPlayer($data['id'], $request->user()->id)) {
      //add in waiting list
    }
    if ($this->touranment->deregisterWaitlist($data['id'], $request->user()->id)) {
      //deregister from waitlist
    }
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
    //get max nimber of player
    // get count of all tournament registeraion
    //waiting player
    //room player
  }

  public function deregisterfromwaiting(TournamentRegisterRequest $request)
  {
    $data = $request->validated();
    //$touranment=$this->touranment->show( ['id' => $data['id']]);
    if ($this->touranment->deregisterPlayerFromWaiting($data['id'], $request->user()->id)) {
      //add in waiting list
    }
    if ($this->touranment->deregisterWaitlist($data['id'], $request->user()->id)) {
      //deregister from waitlist
    }
    return $this->jsonResponseSuccess(
      trans('admin/room/room.create.success')
    );
    //get max nimber of player
    // get count of all tournament registeraion
    //waiting player
    //room player
  }

  public function mytournament(Request $request): TournamentResourceCollection
  {
    $user = $this->user->show(['id' => $request->user()->id]);
    return TournamentResourceCollection::make(
      $user->registeredTournaments()->get()
    );
  }

  public function show(Request $request, $id): TournamentDetailResource
  {
    $data = $this->touranment->show(
      ['slug' => $id]
    );
    if ($data) {
      return new TournamentDetailResource(
        $data
      );
    }

    return $this->jsonResponseFail(
      trans('tournament/tournament/show.create.fail')
    );
  }
}
