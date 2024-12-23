<?php

namespace App\Http\Controllers\Admin\Player;

use App\Http\Controllers\Controller;
use App\Services\User\Player\PlayerService;
use App\Services\Room\RoomService;
use App\Http\Resources\User\Player\PlayerResourceCollection;
use App\Models\User\User;
use App\Models\User\UserProfile;
use App\Models\Room\RoomUser;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Http\Requests\User\Registration\PlayerRegistrationRequest;
use App\Models\Room\RoomManualUser;
use App\Models\Room\RoomMember;
use App\Models\Tournament\TournamentCheckinPlayer;
use App\Models\Tournament\TournamentLateUser;
use App\Models\Tournament\TournamentRebuyCount;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentRegistrationLog;
use App\Models\Tournament\TournamentWaitingPlayer;
use Illuminate\Support\Facades\Hash;

class PlayerController extends Controller
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
    $users = DB::table('users')
      ->select('users.email', 'users.email_verified_at', 'users.status', 'user_profiles.*', DB::raw('count(rooms.id) as room_count'))
      ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
      ->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
      ->leftJoin('room_users', 'room_users.user_id', '=', 'users.id')
      ->leftJoin('rooms', 'rooms.id', '=', 'room_users.room_id')
      ->where('model_has_roles.role_id', '=', '3') // Player
      ->groupBy('user_id')
      ->get();

    return $this->jsonResponseSuccess(['data' => $users]);
  }

  public function store(PlayerRegistrationRequest $request): JsonResponse
  {
    $data = $this->hashPassword($request->validated());
    $data['email_verified_at'] = now();
    $data['status'] = 1;
    $user = $this->user->create($data);

    if ($user) {
      $user->assignRole('Player');
      //add user profile
      $user->profile()->create($data);

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

  public function updateSuspendStatus($userId): JsonResponse
  {
    $user = User::find($userId);
    $user->status = $user->status === 1 ? 2 : 1;
    $user->save();

    $contentVariables = [
      'room_title' => 'Check-Raise',
      'date' => (new Carbon())->format('d.m.Y h:i')
    ];
    $notificationSlug = $user->status === 1 ? 'manager_unsuspend_player' : 'manager_suspend_player';
    sendEmail('player', $notificationSlug, $userId, $contentVariables);

    return $this->jsonResponseSuccess(
      trans('user/registration/update.status.success'),
      200
    );
  }

  /**
   * Update membership date of player.
   */
  public function updateExpiry(Request $request): bool|string
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

  /**
   * Remove customer(player) from the room or update player's first registration date to the room.
   */
  public function updateFirstRegDate(Request $request): bool|string
  {
    $regDate = $request->reg_date;
    $roomId = $request->room_id;
    $userId = $request->user_id;
    $roomUser = RoomUser::where([
      ['room_id', '=', $roomId],
      ['user_id', '=', $userId]
    ])
      ->first();

    // Remove customer from the room.
    if (empty($regDate)) {
      $roomUser->delete();
      RoomMember::where([
        ['room_id', '=', $roomId],
        ['user_id', '=', $userId]
      ])->delete();
    } else if ($regDate === 'before 04.2024') {
      $roomUser->created_at = null;
      $roomUser->save();
    } else {
      $roomUser->created_at = $regDate;
      $roomUser->save();
    }
    $data = array(
      'status' => true,
      'message' => 'First Registration Date Updated!!!'
    );
    return json_encode($data);
  }

  public function storeByRole(PlayerRegistrationRequest $request, $id): JsonResponse
  {
    $data = $this->hashPassword($request->validated());
    $data['email_verified_at'] = now();
    $data['status'] = 1;
    $user = $this->user->create(
      $data
    );
    if ($user) {
      if ($id == 1) {
        $user->assignRole('Admin');
      } else if ($id == 2) {
        $user->assignRole('Room Manager');
      } else if ($id == 3) {
        $user->assignRole('Player');
      } else {
        $user->assignRole('Director');
      }

      //add user profile
      $user->profile()->create($data);

      if ($id == 3) {
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
      }

      return $this->jsonResponseSuccess(
        trans('user/registration/player.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('user/registration/player.create.fail'),
      400
    );
  }

  public function destroy($id): bool|string
  {
    $user = User::find($id);

    RoomManualUser::where('user_id', '=', $user->id)->delete();
    RoomMember::where('user_id', '=', $user->id)->delete();
    RoomUser::where('user_id', '=', $user->id)->delete();
    TournamentCheckinPlayer::where('user_id', '=', $user->id)->delete();
    TournamentLateUser::where('user_id', '=', $user->id)->delete();
    TournamentRebuyCount::where('user_id', '=', $user->id)->delete();
    TournamentRegisterPlayer::where('user_id', '=', $user->id)->delete();
    TournamentWaitingPlayer::where('user_id', '=', $user->id)->delete();
    TournamentRegistrationLog::where('user_id', '=', $user->id)->delete();

    $user->profile()->delete();
    $user->delete();

    if ($user) {
      $response = array(
        'status' => true,
        'message' => 'User Deleted Successfully!!!'
      );
    } else {
      $response = array(
        'status' => true,
        'message' => 'User Not Deleted!!!'
      );
    }
    return json_encode($response);
  }
}
