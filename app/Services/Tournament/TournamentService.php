<?php

namespace App\Services\Tournament;

use App\Models\Common\EmailLog;
use App\Models\Room\RoomUser;
use App\Repositories\Tournament\TournamentRepository;
use App\Services\BaseService;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentRegistrationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentWaitingPlayer;
use App\Models\User\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/*
             public static $STATUS_UNREGISTERED = 1;
            public static $STATUS_WAITING_LIST = 3;
            public static $STATUS_REGISTERED = 2;
                */

class TournamentService extends BaseService
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

  /**
   * Generate unique slug for tournament.
   * @param string $title
   * @param string $startDay
   * @return string
   */
    public function generateSlug(string $title, string $startDay): string
    {
      $title = Str::of($title)->slug('-');
      $date = date('-d-m-Y', strtotime($startDay));
      $slugs = Tournament::where('slug', 'LIKE', $title . '%')->get()->pluck('slug')->all();
      $slug  = $title . $date;

      if(!in_array($slug, $slugs)) {
        return $slug;
      }

      $i = 1;
      $uniqueSlugFound = false;

      while (!$uniqueSlugFound) {
        $slug = $title . '-' . $i . $date;

        if (!in_array($slug, $slugs)) {
          // Unique slug found
          $uniqueSlugFound = true;
          return $slug;
        }

        $i++;
      }

      return $title . '-' . mt_rand(1000,9999) . $date;
    }

    public function registerPlayer($tournamentId, $userId)
    {
        //get max number of players from tournament
        $tournament = $this->show(['id' => $tournamentId]);
        $maxPlayers = $tournament->detail->maxplayers;
        $reservedCount = $tournament->detail->reservedplayers;
        //count register players
        $registeredPlayerCount = $tournament->registeredPlayers()->count();

        // Register add as a player of the room.
        if (!$tournament->room->room_users()->where('user_id', $userId)->first()) {
            RoomUser::create([
                'room_id' => $tournament->room->id,
                'user_id' => $userId,
            ]);
        }

        //check if user is already registered or not
        $isRegistered = $tournament->registeredPlayers()->where('user_id', $userId)->count();

        if ($isRegistered > 0)
            return;

        $room = $tournament->room;
        $contentVariables = [
            'room_title' => $room->title,
            'room_address' => $room->detail->street . "," . $room->detail->zipcode . ' ' . $room->detail->city,
            'tournament_title' => $tournament->title,
            'tournament_date' => (new Carbon($tournament->detail->startday))->format('d.m.Y h:i')
        ];

        if ($reservedCount + $registeredPlayerCount >= $maxPlayers) {
            //self::ajaxWaitingList();
            $this->registerWaitlist($tournamentId, $userId);
            sendEmail('player', 'register_tournament_waiting_list', $userId, $contentVariables);
            return;
        }

        try {
            $tournament->registeredPlayers()->attach($userId);

            sendEmail('player', 'register_tournament', $userId, $contentVariables);

            $logs = $tournament->registeredPlayers()->count();
            /*$logs = DB::table('tournament_registration_logs')
                    ->select('position')
					->where('status_from', '=', 1)
                    ->where('status_from', '=', 1)
					->where('tournament_id', '=', $tournamentId)
					->orderBy('created_at', 'ASC')
                    ->first();*/
            /*if($logs==1){
				$position=1;
			}else{
				$position=$logs+1;
			}*/

            $tournament->registration_log()->create([
                'user_id' => $userId,
                'status_from' => 1,
                'status_to' => 2,
                'added_by' => Auth::user()->roles->pluck('name')[0],
                'position' => $logs
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

        $tournament->registeredPlayers()->detach($UserId);

        $room = $tournament->room;
        $contentVariables = [
            'room_title' => $room->title,
            'room_address' => $room->detail->street . "," . $room->detail->zipcode . ' ' . $room->detail->city,
            'tournament_title' => $tournament->title,
            'tournament_date' => (new Carbon($tournament->detail->startday))->format('d.m.Y h:i')
        ];

        sendEmail('player', 'unregister_tournament', $UserId, $contentVariables);
        /*$logs = DB::table('tournament_registration_logs')
			->select('position')
			->where('status_from', '=', 2)
			->where('status_to', '=', 1)
			->where('tournament_id', '=', $tournamentId)
			->orderBy('created_at', 'ASC')
			->first();*/
        $logs = $tournament->registeredPlayers()->count();
        /*if($logs==1){
			$position=1;
		}else{
			$position=$logs+1;
		}*/
        $tournament->registration_log()->create([
            'user_id' => $UserId,
            'status_from' => 2,
            'status_to' => 1,
            'position' => $logs,
            'added_by' => Auth::user()->roles->pluck('name')[0]
        ]);

        if ($tournament->is_freeze == 1) {
            $tournament->detail()->increment('reservedplayers', 1);
            return;
        }

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
                'status_to' => 8,
                'added_by' => Auth::user()->roles->pluck('name')[0]
            ]);
            $tournament->waitingPlayers()->detach($first_player->pivot->user_id);

            sendEmail(
                'player',
                'enter_tournament_from_waiting',
                $first_player->pivot->user_id,
                $contentVariables
            );
        }
        //$tournament->waitingPlayers()->detach($UserId);
        //

    }
    public function deregisterPlayerFromWaiting($tournamentId, $UserId)
    {

        $tournament = $this->show(['id' => $tournamentId]);
        $first_player = $tournament->waitingPlayers()->first();

        $tournament->registeredPlayers()->detach($UserId);
        $logs = $tournament->registeredPlayers()->count();

        $room = $tournament->room;
        $contentVariables = [
            'room_title' => $room->title,
            'room_address' => $room->detail->street . "," . $room->detail->zipcode . ' ' . $room->detail->city,
            'tournament_title' => $tournament->title,
            'tournament_date' => (new Carbon($tournament->detail->startday))->format('d.m.Y h:i')
        ];

        sendEmail('player', 'unregister_tournament', $UserId, $contentVariables);
        /*if($logs==1){
			$position=1;
		}else{
			$position=$logs+1;
		}*/
        /*$logs = DB::table('tournament_registration_logs')
			->select('position')
			->where('status_from', '=', 0)
			->where('status_to', '=', 7)
			->where('tournament_id', '=', $tournamentId)
			->orderBy('created_at', 'ASC')
			->first();
		if(empty($logs)){
			$position=1;
		}else{
			$position=($logs->position)+1;
		}*/
        $tournament->registration_log()->create([
            'user_id' => $UserId,
            'status_from' => 0,
            'status_to' => 7,
            'position' => $logs,
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
                'status_to' => 8,
                'added_by' => Auth::user()->roles->pluck('name')[0]
            ]);
            $tournament->waitingPlayers()->detach($first_player->pivot->user_id);

            sendEmail(
                'player',
                'enter_tournament_from_waiting',
                $first_player->pivot->user_id,
                $contentVariables
            );
        }
        //$tournament->waitingPlayers()->detach($UserId);
        //

    }
    public function registerWaitlist($tournamentId, $UserId)
    {
        $tournament = $this->show(['id' => $tournamentId]);
        $tournament->waitingPlayers()->attach($UserId);
        $logs = $tournament->waitingPlayers()->count();
        /*if($logs==1){
			$position=1;
		}else{
			$position=$logs+1;
		}*/
        /*$logs = DB::table('tournament_registration_logs')
			->select('position')
			->where('status_from', '=', 1)
			->where('status_to', '=', 3)
			->where('tournament_id', '=', $tournamentId)
			->orderBy('created_at', 'ASC')
			->first();
		if(empty($logs)){
			$position=1;
		}else{
			$position=($logs->position)+1;
		}*/

        $tournament->registration_log()->create([
            'user_id' => $UserId,
            'status_from' => 1,
            'status_to' => 3,
            'position' => $logs,
            'added_by' => Auth::user()->roles->pluck('name')[0]
        ]);
    }
    public function deregisterWaitlist($tournamentId, $UserId)
    {
        $tournament = $this->show(['id' => $tournamentId]);
        $tournament->waitingPlayers()->detach($UserId);
        /*$tournament->registration_log()->create([
            'user_id'=>$UserId,
            'status_from'=>3,
            'status_to'=>1,
			'added_by'=>Auth::user()->roles->pluck('name')[0]
        ]);*/
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
        $tournament->rebuycount()->where('user_id', $UserId)->delete();
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
        $registeredPlayersCount = $tournament->registeredPlayers()->count();
        $maxPlayersCount = $tournament->detail->maxplayers;
        $reservedCount = $tournament->detail->reservedplayers;
        //check witlist to register
        if ($reservedCount + $registeredPlayersCount <= $maxPlayersCount) {
            //add this new user from waiting list into  register table
            //get first wait list player
            $leftCount = $maxPlayersCount - $reservedCount - $registeredPlayersCount;
            if ($leftCount < 1) return;

            $waitingPlayers = $tournament->waitingPlayers()->orderBy('pivot_created_at')->limit($leftCount)->get();

            if (empty($waitingPlayers)) return;

            $room = $tournament->room;
            $contentVariables = [
              'room_title' => $room->title,
              'room_address' => $room->detail->street . "," . $room->detail->zipcode . ' ' . $room->detail->city,
              'tournament_title' => $tournament->title,
              'tournament_date' => (new Carbon($tournament->detail->startday))->format('d.m.Y h:i')
            ];

            foreach ($waitingPlayers as $waitingPlayer) {
                $tr =  new TournamentRegisterPlayer;
                $tr->tournament_id = $tournamentId;
                $tr->user_id = $waitingPlayer->pivot->user_id;
                $tr->created_at = $waitingPlayer->pivot->created_at;
                $tr->updated_at = date("Y-m-d h:i:s");
                $tr->save();

                //make a log of user registration
                $logs = $tournament->registeredPlayers()->count();
                $tournament->registration_log()->create([
                    'user_id' => $waitingPlayer->pivot->user_id,
                    'status_from' => 3,
                    'status_to' => 8,
                    'added_by' => Auth::user()->roles->pluck('name')[0],
                    'position' => $logs
                ]);
                $tournament->waitingPlayers()->detach($waitingPlayer->pivot->user_id);

                sendEmail(
                  'player',
                  'enter_tournament_from_waiting',
                  $waitingPlayer->pivot->user_id,
                  $contentVariables
                );
            }
        } else {
            $leftCount = $reservedCount + $registeredPlayersCount - $maxPlayersCount;
            if ($leftCount < 1) return;

            $registeredPlayers = $tournament->registeredPlayers()->orderByDesc('pivot_created_at')->limit($leftCount)->get();
            if (empty($registeredPlayers)) return;

            foreach ($registeredPlayers as $registeredPlayer) {
                $tr =  new TournamentWaitingPlayer;
                $tr->tournament_id = $tournamentId;
                $tr->user_id = $registeredPlayer->pivot->user_id;
                $tr->created_at = $registeredPlayer->pivot->created_at;
                $tr->updated_at = date("Y-m-d h:i:s");
                $tr->save();

                $logs = $tournament->waitingPlayers()->count();

                $tournament->registration_log()->create([
                    'user_id' => $registeredPlayer->pivot->user_id,
                    'status_from' => 2,
                    'status_to' => 3,
                    'added_by' => Auth::user()->roles->pluck('name')[0],
                    'position' => $logs
                ]);
                $tournament->registeredPlayers()->detach($registeredPlayer->pivot->user_id);
            }
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

    public function getPlayers($tournamentId, $keyword)
    {
        $likeKeyword = '%' . $keyword . '%';
        $tournament = $this->show(['id' => $tournamentId]);
        // All tournaments in same room
        // $tournaments = $tournament->room->tournaments()->get();

        // array of id of checked players of all tournaments in same room.
        // $playerIds = [];
        // foreach($tournaments as $t) {
        //     $playerIds = array_merge($playerIds, $t->checkinPlayers()->get()->pluck('id')->toArray());
        // }

        // all user ids of room
        $roomUserIds = RoomUser::where('room_id', '=', $tournament->room_id)->get()->pluck('user_id')->toArray();

        $query = User::role('Player')->with('profile');

        $query = $query->whereIn('id', $roomUserIds);

        if (!empty($keyword)) {
            // $query = $query->whereHas('profile', function ($q) use ($likeKeyword) {
            //     $q->where('firstname', 'LIKE', $likeKeyword)
            //         ->orWhere('lastname', 'LIKE', $likeKeyword)
            //         ->orWhere('nickname', 'LIKE', $likeKeyword);
            // })
            //     ->where('email', 'LIKE', $likeKeyword) // email of customers of the room.
            //     ->orWhere('email', '=', $keyword); // email of non-customers
            $query = $query->where(function ($q) use ($likeKeyword) {
                $q->whereHas('profile', function ($profileQuery) use ($likeKeyword) {
                    $profileQuery->where('firstname', 'LIKE', $likeKeyword)
                        ->orWhere('lastname', 'LIKE', $likeKeyword)
                        ->orWhere('nickname', 'LIKE', $likeKeyword);
                })
                    ->orWhere('email', 'LIKE', $likeKeyword); // email of customers of the room.
            })
                ->orWhere('email', '=', $keyword);
        }

        return $query->paginate(100);
    }
}
