<?php

namespace App\Services\User\Player;

use App\Repositories\Tournament\TournamentRepository;
use App\Services\BaseService;
use App\Models\Tournament\Tournament;
use Illuminate\Support\Facades\Auth;

/*
             public static $STATUS_UNREGISTERED = 1;
            public static $STATUS_WAITING_LIST = 3;
            public static $STATUS_REGISTERED = 2;
                */

class PlayerService extends BaseService
{
  /**
   * @var TournamentRepository
   */
  protected TournamentRepository $tournament;

  /**
   * @param TournamentRepository $tournament
   */
  public function __construct(TournamentRepository $tournament)
  {
    $this->tournament = $tournament;
    parent::__construct($this->tournament);
  }

  public function registerPlayer($tournamentId, $UserId)
  {
    //get max number of players from tournament
    $tournament = $this->show(['id' => $tournamentId]);
    //count register players
    $users = $tournament->registeredPlayers()->count();
    $maxPlayers = $tournament->detail->maxplayers;
    $reservedCount = $tournament->detail->reservedplayers;
    //check if user is already registered or not

    $isregistered = $tournament->registeredPlayers()->where('user_id', $UserId)->count();
    if ($isregistered > 0)
      return;

    if ($reservedCount + $users >= $maxPlayers) {
      //self::ajaxWaitingList();
      $this->registerWaitlist($tournamentId, $UserId);
      //$tournament->room->room_users()->attach($UserId);
      return;
      //return false;

    }
    try {
      $tournament->registeredPlayers()->attach($UserId);
      //make a log of user registration
      $tournament->registration_log()->create([
        'user_id' => $UserId,
        'status_from' => 1,
        'status_to' => 2,
        'added_by' => Auth::user()->roles->pluck('name')[0]
      ]);
      //also add user attach in room
      //$tournament->room->room_users()->attach($UserId);
      //
    } catch (\Illuminate\Database\QueryException $ex) {
      return false;
    }

  }

  public function deregisterPlayer($tournamentId, $UserId)
  {

    $tournament = $this->show(['id' => $tournamentId]);
    $first_player = $tournament->waitingPlayers()->first();

    $tournament->registeredPlayers()->detach($UserId);
    $tournament->registration_log()->create([
      'user_id' => $UserId,
      'status_from' => 2,
      'status_to' => 1,
      'added_by' => Auth::user()->roles->pluck('name')[0]
    ]);
    ////update waiting list
    $users = $tournament->registeredPlayers()->count();
    $maxPlayers = $tournament->detail->maxplayers;
    $reservedCount = $tournament->detail->reservedplayers;
    $isregistered = $tournament->registeredPlayers()->where('user_id', $UserId)->count();
    if ($isregistered > 0)
      return;
    if ($reservedCount + $users < $maxPlayers) {
      //add this new user from waiting list into  register table
      //get first wait list player

      $first_player = $tournament->waitingPlayers()->first();
      if (empty($first_player))
        return;
      //attach
      $tournament->registeredPlayers()->attach($first_player->pivot->user_id);
      //make a log of user registration
      $tournament->registration_log()->create([
        'user_id' => $first_player->pivot->user_id,
        'status_from' => 3,
        'status_to' => 2,
        'added_by' => Auth::user()->roles->pluck('name')[0]
      ]);
      $tournament->waitingPlayers()->detach($first_player->pivot->user_id);
    }
    //$tournament->waitingPlayers()->detach($UserId);
    //

  }

  public function registerWaitlist($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $tournament->waitingPlayers()->attach($UserId);
    $tournament->registration_log()->create([
      'user_id' => $UserId,
      'status_from' => 1,
      'status_to' => 3,
      'added_by' => Auth::user()->roles->pluck('name')[0]
    ]);

  }

  public function deregisterWaitlist($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $tournament->waitingPlayers()->detach($UserId);
    $tournament->registration_log()->create([
      'user_id' => $UserId,
      'status_from' => 3,
      'status_to' => 1,
      'added_by' => Auth::user()->roles->pluck('name')[0]
    ]);
  }

  public function checkinPlayer($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $tournament->checkinPlayers()->attach($UserId);

  }

  public function cancelcheckinPlayer($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $tournament->checkinPlayers()->detach($UserId);
  }


  public function plusrebuy($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $rebuycheck = $tournament->rebuycount()->where(['user_id' => $UserId])->first();
    if (empty($rebuycheck))
      $tournament->rebuycount()->create(['user_id' => $UserId, 'rebuycount' => 1]);
    else
      $rebuycheck->increment('rebuycount');

    return;

  }

  public function minusrebuy($tournamentId, $UserId)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $rebuycheck = $tournament->rebuycount()->where(['user_id' => $UserId])->first();
    if (empty($rebuycheck))
      return;
    if ($rebuycheck->rebuycount <= 1) {
      $rebuycheck->delete();
      return;
    }
    $rebuycheck->decrement('rebuycount');
    return;
  }

  public function updateCounts($tournamentId, $maxplayers, $reservedplayers)
  {
    $tournament = $this->show(['id' => $tournamentId]);
    $tournament->detail->update([
      'maxplayers' => $maxplayers,
      'reservedplayers' => $reservedplayers
    ]);
    $tournament = $this->show(['id' => $tournamentId]);
    $users = $tournament->registeredPlayers()->count();
    $maxPlayers = $tournament->detail->maxplayers;
    $reservedCount = $tournament->detail->reservedplayers;
    //check witlist to register
    if ($reservedCount + $users < $maxPlayers) {
      //add this new user from waiting list into  register table
      //get first wait list player

      $first_player = $tournament->waitingPlayers()->first();
      if (empty($first_player))
        return;
      //attach
      $tournament->registeredPlayers()->attach($first_player->pivot->user_id);
      //make a log of user registration
      $tournament->registration_log()->create([
        'user_id' => $first_player->pivot->user_id,
        'status_from' => 3,
        'status_to' => 2,
        'added_by' => Auth::user()->roles->pluck('name')[0]
      ]);
      $tournament->waitingPlayers()->detach($first_player->pivot->user_id);
    }
  }

  public function checkout($id)
  {
    $tournament = $this->show(['id' => $id]);
    //check in players
    //update status
    $this->update($id, ['closed' => 1]);
    //registered players
    $registeredplayers = $tournament->registeredPlayers()->pluck('user_id')->toArray();
    $checkinplayers = $tournament->checkinPlayers()->pluck('user_id')->toArray();
    $notCheckedInPlayers = array_diff($registeredplayers, $checkinplayers);

    //remove from registered table
    $tournament->registeredPlayers()->detach($notCheckedInPlayers);

    //clear waiting list
    $tournament->waitingPlayers()->delete();
    return;
    //delete waiting players

  }

}
