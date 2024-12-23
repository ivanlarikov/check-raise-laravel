<?php

namespace App\Http\Controllers\User\MyPlayer;

use App\Http\Controllers\Controller;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentCheckinPlayer;
use App\Models\Tournament\TournamentLateUser;
use App\Models\Tournament\TournamentRebuyCount;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentWaitingPlayer;
use App\Models\User\User;
use App\Services\User\Player\PlayerService;
use App\Services\Room\RoomService;
use App\Models\User\UserProfile;
use App\Models\Room\RoomUser;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Http\Requests\User\Registration\PlayerRegistrationRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MyPlayerController extends Controller
{
  use ResponseTrait;

  /**
   * @var PlayerService
   */
  protected PlayerService $player;
  protected RoomService $room;
  protected UserService $user;

  /**
   * @param PlayerService $player
   * @param RoomService $room
   * @param UserService $user
   */
  public function __construct(PlayerService $player, RoomService $room, UserService $user)
  {
    $this->player = $player;
    $this->room = $room;
    $this->user = $user;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $managerId = $request->user()->id;
    $room = $this->room->show(['user_id' => $managerId]);

    $finishedTournamentIds = Tournament::where('room_id', '=', $room->id)
      ->whereHas('detail', function ($q) {
        $q->where('startday', '<', Carbon::now()->setHour(8)->setMinute(0));
      })
      ->get()->pluck('id');

    $query = RoomUser::select(
      'users.id as id',
      'users.email as email',
      'user_profiles.firstname as firstname',
      'user_profiles.lastname as lastname',
      'user_profiles.nickname as nickname',
      'user_profiles.city as city',
      'user_profiles.zipcode as zipcode',
      'is_suspend',
      'room_users.room_id as room_id',
      'room_users.created_at as first_registration_date',
      'room_members.id as room_member_id',
      'room_members.expiry as membership',
      DB::raw('COUNT(tournament_checkin_players.user_id) as with_checkin'),
      DB::raw('COUNT(finished_tournament_register_players.id) - COUNT(tournament_checkin_players.user_id) as without_checkin'),
      DB::raw('MAX(tournament_register_players.created_at) as last'),
    )
      ->leftJoin('users', 'users.id', '=', 'room_users.user_id')
      ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
      ->leftJoin('room_members', function ($join) {
        $join->on('room_members.room_id', '=', 'room_users.room_id');
        $join->on('room_members.user_id', '=', 'room_users.user_id');
      })
      ->leftJoin('tournaments', 'tournaments.room_id', '=', 'room_users.room_id')
      ->leftJoin('tournament_register_players', function ($join) {
        $join->on('tournament_register_players.tournament_id', '=', 'tournaments.id');
        $join->on('tournament_register_players.user_id', '=', 'room_users.user_id');
      })
      ->leftJoin('tournament_checkin_players', function ($join) use ($finishedTournamentIds) {
        $join->on('tournament_checkin_players.tournament_id', '=', 'tournaments.id');
        $join->on('tournament_checkin_players.user_id', '=', 'room_users.user_id');
        $join->whereIn('tournament_checkin_players.tournament_id', $finishedTournamentIds);
      })
      ->leftJoin('tournament_register_players as finished_tournament_register_players', function ($join) use ($finishedTournamentIds) {
        $join->on('finished_tournament_register_players.tournament_id', '=', 'tournaments.id');
        $join->on('finished_tournament_register_players.user_id', '=', 'room_users.user_id');
        $join->whereIn('finished_tournament_register_players.tournament_id', $finishedTournamentIds);
      })
      ->where('room_users.room_id', '=', $room->id)
      ->groupBy('room_users.user_id')
      ->orderBy('room_users.created_at', 'desc');

    $users = $query->get();
    $total = $users->count();

    return $this->jsonResponseSuccess([
      'data' => [
        'room_id' => $room->id,
        'users' => [
          'total' => $total,
          'data' => $users
        ]
      ]
    ]);
  }

  public function getTotalCount(Request $request): JsonResponse
  {
    $room = $this->room->show(['user_id' => $request->user()->id]);
    return $this->jsonResponseSuccess([
      'total' => $room->room_users->count()
    ]);
  }

  public function store(PlayerRegistrationRequest $request): JsonResponse
  {
    $data = $this->hashPassword($request->validated());
    $data['email_verified_at'] = now();
    $data['status'] = 1;
    $user = $this->user->create(
      $data
    );
    if ($user) {

      $user->assignRole('Player');
      //add user profile
      $user->profile()->create($data);
      $roomUser = new RoomUser();
      //On left field name in DB and on right field name in Form/view/request
      $roomUser->room_id = $request->user()->room->id;
      $roomUser->user_id = $user->id;
      $roomUser->save();

      sendEmail(
        'player',
        'manual_register',
        $user->id,
        [
          'firstname' => $data['firstname'],
          'lastname' => $data['lastname'],
          'email' => $data['email'],
          'password' => $request->input('password')
        ]
      );

      $languages = ['en' => 'English', 'fr' => 'French', 'de' => 'German'];
      $displayOptions = ['public_nic' => 'Name Surname', 'private' => "Nickname (If applicable)", 'anonymous' => 'Anonymous'];

      $data['address'] = $data['street'] . "," . $data['zipcode'] . ' ' . $data['city'];
      $data['dob'] = (new Carbon($data['dob']))->format('d.m.Y');
      $data['phone'] = $data['phonecode'] . ' ' . $data['phonenumber'];
      $data['displayoption'] = $displayOptions[$data['displayoption']];
      $data['language'] = $languages[$data['language']];
      sendEmail(
        'admin',
        'new_player',
        $user->id,
        $data
      );

      return $this->jsonResponseSuccess(
        trans('user/registration/player.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('user/registration/player.create.fail'),
      400
    );
  }

  protected function hashPassword(array $data): array
  {
    $data['password'] = Hash::make($data['password']);
    return $data;
  }

  public function saveexcel($id): bool|JsonResponse|string
  {

    $csvFileName = time() . '.xls';
    $headers = [
      'Content-Type' => 'application/vnd.ms-excel',
      'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
    ];

    $userprofiles = UserProfile::where('user_id', "=", $id)->get();
    if ($userprofiles) {

      $handle = fopen($csvFileName, 'w');
      fputcsv($handle, ['id']); // Add more headers as needed

      foreach ($userprofiles as $userprofile) {
        fputcsv($handle, [$userprofile->id]); // Add more fields as needed
      }

      fclose($handle);
      $response = array(
        'file' => $csvFileName,
        'status' => 'File Save Success!!!'
      );
      return json_encode($response);
    }
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.not.found')
    );
  }

  public function updateSuspendStatus(Request $request, $userId): JsonResponse
  {
    $roomId = $request->user()->room->id;
    $roomUser = RoomUser::where('room_id', $roomId)->where('user_id', $userId)->first();
    $user = User::find($userId);

    $contentVariables = [
      'room_title' => $request->user()->room->title,
      'date' => (new Carbon())->format('d.m.Y h:i')
    ];
    $languages = ['en' => 'English', 'fr' => 'French', 'de' => 'German'];
    $displayOptions = ['public_nic' => 'Name Surname', 'private' => "Nickname (If applicable)", 'anonymous' => 'Anonymous'];

    $adminMailContent = $user->profile->toArray();
    $adminMailContent['room_title'] = $request->user()->room->title;
    $adminMailContent['email'] = $user->email;
    $adminMailContent['dob'] = (new Carbon($adminMailContent['dob']))->format('d.m.Y');
    $adminMailContent['phone'] = $adminMailContent['phonecode'] . ' ' . $adminMailContent['phonenumber'];
    $adminMailContent['address'] = $adminMailContent['street'] . "," . $adminMailContent['zipcode'] . ' ' . $adminMailContent['city'];
    $adminMailContent['displayoption'] = $displayOptions[$adminMailContent['displayoption']];
    $adminMailContent['language'] = $languages[$adminMailContent['language']];

    if ($roomUser->is_suspend == 0) {
      $roomUser->is_suspend = 1;
      sendEmail('player', 'manager_suspend_player', $userId, $contentVariables);
      sendEmail('admin', 'rm_suspend', null, $adminMailContent);

      // Un-register from all tournaments when suspended.
      $tournamentIds = Tournament::where('room_id', $roomId)
        ->whereHas('detail', function ($q) {
          $q->where('startday', '>', Carbon::now()->setHour(8)->setMinute(0));
        })
        ->get()->pluck('id');
      TournamentCheckinPlayer::where('user_id', $userId)->whereIn('tournament_id', $tournamentIds)->delete();
      TournamentLateUser::where('user_id', $userId)->whereIn('tournament_id', $tournamentIds)->delete();
      TournamentRebuyCount::where('user_id', $userId)->whereIn('tournament_id', $tournamentIds)->delete();
      TournamentRegisterPlayer::where('user_id', $userId)->whereIn('tournament_id', $tournamentIds)->delete();
      TournamentWaitingPlayer::where('user_id', $userId)->whereIn('tournament_id', $tournamentIds)->delete();
    } else {
      $roomUser->is_suspend = 0;
      sendEmail('player', 'manager_unsuspend_player', $userId, $contentVariables);
      sendEmail('admin', 'rm_unsuspend', null, $adminMailContent);
    }
    $roomUser->update();

    return $this->jsonResponseSuccess(
      trans('user/registration/update.status.success'),
      200
    );
  }
}
