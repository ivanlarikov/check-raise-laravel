<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tournament\TournamentTableResourceCollection;
use App\Models\User\User;
use App\Services\Tournament\TournamentService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Resources\Tournament\TournamentQuickViewResource;
use App\Http\Resources\Tournament\TournamentDetailResource;
use App\Http\Requests\Tournament\TournamentCreateRequest;
use App\Http\Requests\Tournament\TournamentUpdateRequest;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    use ResponseTrait;
    /**
     * @var TournamentService
     */
    protected TournamentService $tournment;

    /**
     * @param TournamentService $tournment
     */
    public function __construct(TournamentService $tournment)
    {
        $this->tournment = $tournment;
    }

    /**
     * @param Request $request
     * @return TournamentTableResourceCollection
     */
    public function index(Request $request)
    {
      $roomIds = $request->input('rooms');
      $query = Tournament::with('room.setting')
        ->withCount('rebuycount', 'registeredPlayers', 'waitingPlayers', 'checkinPlayers')
        ->where([
          'status' => 1,
          'closed' => 0,
          'archived' => 0
        ]);

      if (!empty($roomIds)) {
        $query = $query->whereHas('room', function ($q) use ($roomIds) {
          $q->whereIn('id', $roomIds);
        });
      }

      $query = $query->whereHas('detail', function ($q) {
        $q->whereBetween('startday', [
          date('Y-m-d') . ' 00:00:00',
          date('Y-m-d', strtotime('+1 year')) . ' 00:00:00'
        ]);
      });

      $tournaments = $query->get();

      $userId = $request->user() ? $request->user()->id : $request->input('userId');
      $user = User::find($userId);

      $request->request->add([
        'user' => $user
      ]);

      return TournamentTableResourceCollection::make($tournaments);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return TournamentDetailResource | JsonResponse
     */
    public function show(Request $request, $id): TournamentDetailResource | JsonResponse
    {
        $data = $this->tournment->show(
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

    public function getDescription(Request $request, $id) {
      $tournament = $this->tournment->show(['slug' => $id]);

      return $this->jsonResponseSuccess([
        'data' => $tournament->description
      ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function table($id)
    {
        $data = $this->tournment->show(
            ['id' => $id]
        );
        if ($data) {
            return new TournamentQuickViewResource(
                $data
            );
        }

        return $this->jsonResponseFail(
            trans('tournament/tournament/show.create.fail')
        );
    }

  /**
   * @param Request $request
   * @return TournamentTableResourceCollection
   */
  public function getFilterData(Request $request): TournamentTableResourceCollection
  {
    $token = $request->bearerToken();
    $user = null;
    if ($token) {
      [$id, $userToken] = explode('|', $token, 2);
      $tokenData = DB::table('personal_access_tokens')
        ->where([
          'id' => $id,
          'token' => hash('sha256', $userToken),
        ])
        ->first();
      $user = User::find($tokenData->tokenable_id);
    }

    $request->user = $user;

    $startDate = $request->input('from') == 'null' ? '' : $request->input('from');
    $endDate = $request->input('to') == 'null' ? '' : $request->input('to');
    $minBuyin = $request->input('minBuyin') ?? 0;
    $maxBuyin = $request->input('maxBuyin') ?? 300;
    $minPlayers = $request->input('minPlayers') ?? 0;
    $maxPlayers = $request->input('maxPlayers') ?? 100;
    $reentry = $request->input('reentry') ?? 3;
    $roomIds = $request->input('roomIds');
    $dealerType = $request->input('dealertype');

    $query = Tournament::select(
        'tournaments.*',
        DB::raw('count(tournament_register_players.user_id) + tournament_details.reservedplayers as players_count')
      )
      ->with('room.setting')
      ->withCount('rebuycount', 'registeredPlayers', 'waitingPlayers', 'checkinPlayers')
      ->join('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
      ->leftJoin('tournament_register_players', 'tournament_register_players.tournament_id', '=', 'tournaments.id');

    // Filter by buyin
    if ($minBuyin > 0 || $maxBuyin < 300) {
      $query = $query->whereBetween('tournament_details.buyin', [$minBuyin, $maxBuyin]);
    }

    // Filter by Registered Players
    if ($minPlayers > 0 || $maxPlayers < 100) {
      $query = $query->having('players_count', '>=', $minPlayers)
        ->having('players_count', '<=', $maxPlayers);
    }

    // Filter by reentry
    if (!empty($reentry) && $reentry != 3) {
      if ($reentry == 2)
        $query = $query->where('tournament_details.maxreentries', '=', 0);
      else
        $query = $query->where('tournament_details.maxreentries', '>', 0);
    }

    // Filter by dealer type
    if (!empty($dealerType)) {
      $query = $query->where('tournament_details.dealertype', '=', $dealerType);
    }

    // Filter by room
    if (!empty($roomIds)) {
      $roomIdsArr = explode(",", $roomIds);
      $query = $query->whereIn('tournaments.room_id', $roomIdsArr);
    }

    // Filter by start date
    if (!empty($startDate) && !empty($endDate)) {
      $query = $query->whereBetween('tournament_details.startday', [date($startDate) . ' 00:00:00', date($endDate) . ' 23:59:59']);
    } else {
      $query = $query->whereBetween('tournament_details.startday', [date('Y-m-d') . ' 00:00:00', date('Y-m-d', strtotime('+1 year')) . ' 00:00:00']);
    }
    $tournaments = $query->where(['tournaments.status' => 1, 'tournaments.closed' => 0, 'tournaments.archived' => 0])
      ->orderBy('tournament_details.startday', 'asc')
      ->groupBy('tournaments.id')
      ->get();

    return TournamentTableResourceCollection::make($tournaments);
  }
}
