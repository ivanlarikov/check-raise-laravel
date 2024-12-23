<?php

namespace App\Http\Controllers\User\Tournament\Log;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;

use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentLog;
use App\Traits\Response\ResponseTrait;
use App\Models\Room\Room;

use App\Http\Resources\Tournament\Log\TournamentLogResourceCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
  use ResponseTrait;

  /**
   * @param TournamentService $tournment
   */
  public function __construct()
  {
  }

  public function index(Request $request)
  {
    $roomId = $request->user()->room->id;
    // $tournamentIds = Tournament::where('room_id', '=', $roomId)->get()->pluck('id')->toArray();

    $query = TournamentLog::select(
      'tournament_logs.id as id',
      'changes',
      'tournament_logs.type as type',
      'tournament_logs.created_at as datetime',
      'tournaments.title as tournament',
      'tournament_details.startday as tournament_date',
      'tournaments.status as status',
      DB::raw('CONCAT(user_profiles.firstname, " ", user_profiles.lastname) as manager'),
    )
      ->leftJoin('tournaments', 'tournaments.id', '=', 'tournament_logs.tournament_id')
      ->leftJoin('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
      ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'tournament_logs.user_id')
      // ->whereIn('tournament_logs.tournament_id', $tournamentIds)
      ->where('tournament_logs.room_id', $roomId)
      ->orderBy('tournament_logs.created_at', 'desc');

    $tournamentLogs = $query->get();
    $total = $query->count();

    return $this->jsonResponseSuccess([
      'data' => $tournamentLogs,
      'total' => $total,
    ]);

    // $roomIds = $request->user()->room()->pluck('id')->toArray();
    // $tournaments = Tournament::whereIn('room_id', $roomIds)->get()->pluck('id')->toArray();
    // $tournamentLogs = TournamentLog::whereIn('tournament_id', $tournaments)->limit(0)->get();

    // return TournamentLogResourceCollection::make(
    //   $tournamentLogs
    // );
  }
}
