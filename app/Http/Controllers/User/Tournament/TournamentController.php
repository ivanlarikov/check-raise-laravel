<?php

namespace App\Http\Controllers\User\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Tournament\TournamentCheckinPlayer;
use App\Models\Tournament\TournamentRebuyCount;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentRegistrationLog;
use App\Models\Tournament\TournamentStructure;
use App\Models\Tournament\TournamentWaitingPlayer;
use App\Services\Tournament\TournamentService;
use App\Services\Room\RoomService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Requests\User\Tournament\TournamentCreateRequest;
use App\Http\Requests\User\Tournament\TournamentUpdateStatusRequest;
use App\Http\Requests\User\Tournament\TournamentUpdateRequest;
use App\Http\Requests\User\Tournament\TournamentDuplicateRequest;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentLateUser;
use App\Models\Room\Template\Template;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Room\Room;
use App\Models\Tournament\TournamentLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TournamentController extends Controller
{
  use ResponseTrait;

  /**
   * @var TournamentService
   */
  protected TournamentService $tournament;
  protected RoomService $room;

  /**
   * @param TournamentService $tournament
   * @param RoomService $room
   */
  public function __construct(TournamentService $tournament, RoomService $room)
  {
    $this->tournament = $tournament;
    $this->room = $room;
  }

  /**
   * @param Request $request
   * @return TournamentResourceCollection
   */
  public function index(Request $request): TournamentResourceCollection
  {
    //check if rooms are set
    //Get tournament IDs
    $tournaments = DB::table('tournaments')
      ->select('tournaments.id')
      ->join('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
      ->join('rooms', 'rooms.id', '=', 'tournaments.room_id');

    $tournaments = $tournaments->where(['tournaments.closed' => 0, 'tournaments.archived' => 0])->where('rooms.user_id', $request->user()->id)
      ->orderBy('tournaments.id', 'desc')
      ->get()->pluck('id')->toArray();
    return TournamentResourceCollection::make(
      Tournament::whereIn('id', $tournaments)->get()
    );
  }

  public function store(TournamentCreateRequest $request): JsonResponse
  {
    $data = $request->validated();
    if (!$this->room->isOwner($request->user(), $data['tournament']['room_id']))
      return $this->jsonResponseFail(
        trans('user/tournament/tournament.create.fail')
      );

    $roomDetails = Room::find($data['tournament']['room_id']);

    $buyin = intval($data['details']['buyin']);
    $bounty = intval($data['details']['bounty']);
    $maxreentries = intval($data['details']['maxreentries']);
    $reentry = intval($data['details']['reentry']);
    $reentryBounty = intval($data['details']['reentry_bounty']);
    $reentrySum = $reentry + $reentryBounty;
    $limit = $buyin + $bounty + ($maxreentries * $reentrySum);
    $limitWithoutReEntry = $buyin + $bounty;

    if ($roomDetails->buyuinlimit < $limit || $roomDetails->buy_in_limit_without_reentry < $limitWithoutReEntry) {
      return $this->jsonResponseFail(
        trans("Can't add tournament.Limit of buy-in reached!!!"),
        200
      );
    }
    $startday = $data['details']['startday'];
    $lastday = $data['details']['lastday'];
    if (!empty($lastday)) {
      if ($startday > $lastday) {
        return $this->jsonResponseFail(
          trans('End of tournament cannot be before the start date!!'),
          200
        );
      }
    }
    if ($data['tournament']['room_id']) {
      $isroomstatus = Room::find($data['tournament']['room_id']);
      if ($isroomstatus->status == "1") {
        //$data['tournament']['status']=1;
        $data['tournament']['status'] = 0;
      } else {
        $data['tournament']['status'] = 0;
      }
    } else {
      $data['tournament']['status'] = 0;
    }

    $data['tournament']['slug'] = $this->tournament->generateSlug($data['tournament']['title'], $data['details']['startday']);
    $data['tournament']['user_id'] = $request->user()->id;
    //create tournament
    $tournament = $this->tournament->create(
      $data['tournament']
    );

    if ($tournament) {
      $tournament->detail()->create($data['details']);
      /* updateOrCreate description */
      foreach ($data['descriptions'] as $key => $item) {
        $tournament->description()->updateOrCreate(
          ['language' => $item['language']],
          ['language' => $item['language'], 'description' => $item['description']]
        );
      }

      /* updateOrCreate Structure */
      if (!empty($data['structure'])) {
        foreach ($data['structure'] as $key => $item) {
          $tournament->structure()->updateOrCreate(
            ['order' => $item['order']],
            ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
          );
        }
      }

      sendEmail(
        'admin',
        'new_tournament',
        null,
        [
          'room_title' => $roomDetails->title,
          'tournament_title' => $tournament->title,
          'tournament_date' => (new Carbon($data['details']['startday']))->format('d.m.Y'),
        ]
      );
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('tournament/tournament.create.fail'),
      400
    );
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function show(int $id): JsonResponse|TournamentResource
  {

    $data = $this->tournament->show(
      ['id' => $id]
    );
    if ($data) {
      return new TournamentResource(
        $data
      );
    }

    return $this->jsonResponseFail(
      trans('tournament/tournament/show.create.fail')
    );
  }

  /**
   * Update the specified resource in storage.
   *
   * @param TournamentUpdateRequest $request
   * @return JsonResponse
   * @throws Exception
   */
  public function update(TournamentUpdateRequest $request): JsonResponse
  {
    $data = $request->validated();
    $roomDetails = Room::find($data['tournament']['room_id']);
    $startday = $data['details']['startday'];
    $lastday = $data['details']['lastday'];
    if (!empty($lastday)) {
      if ($startday > $lastday) {
        return $this->jsonResponseFail(
          trans('End of tournament cannot be before the start date!!'),
          200
        );
      }
    }

    $buyin = intval($data['details']['buyin']);
    $bounty = intval($data['details']['bounty']);
    $maxreentries = intval($data['details']['maxreentries']);
    $reentry = intval($data['details']['reentry']);
    $reentryBounty = intval($data['details']['reentry_bounty']);
    $reentrySum = $reentry + $reentryBounty;
    $limit = $buyin + $bounty + ($maxreentries * $reentrySum);
    $limitWithoutReEntry = $buyin + $bounty;

    if ($roomDetails->buyuinlimit < $limit || $roomDetails->buy_in_limit_without_reentry < $limitWithoutReEntry) {
      return $this->jsonResponseFail(
        trans("Can't update tournament.Limit of buy-in reached!!!"),
        200
      );
    }

    $room = Room::find($data['tournament']['room_id']);
    if ($room->status != 1 || (new \DateTime($startday))->format('Y-m-d') > (new \DateTime($room->expiry))->format('Y-m-d')) {
      $data['tournament']['status'] = 0;
    }

    $data['tournament']['user_id'] = $request->user()->id;
    $tournament = $this->tournament->update(
      $data['tournament']['id'],
      $data['tournament']
    );
    /* update details */
    $tournament = $this->tournament->show(
      ['id' => $data['tournament']['id']]
    );

    if ($tournament) {
      if ($tournament->status != 1) {
        $slug = $this->tournament->generateSlug($data['tournament']['title'], $data['details']['startday']);
        $tournament->update(['slug' => $slug]);
      }

      //$tournament->description()->create($data);
      $tournament->detail->update($data['details']);

      /* updateOrCreate description */
      foreach ($data['descriptions'] as $key => $item) {
        $tournament->description()->updateOrCreate(
          ['language' => $item['language']],
          ['language' => $item['language'], 'description' => $item['description']]
        );
      }
      if (!empty($data['structure'])) {
        $tournament->structure()->delete();
        /* updateOrCreate Structure */
        foreach ($data['structure'] as $key => $item) {
          $tournament->structure()->updateOrCreate(
            ['order' => $item['order']],
            ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
          );
        }
      }
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.update.success')
      );
    }

    return $this->jsonResponseFail(
      trans('tournament/tournament.update.fail'),
      400
    );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function destroy(Request $request, $id): JsonResponse
  {
    $tournament = Tournament::find($id);

    TournamentDescription::where('tournament_id', "=", $id)->delete();
    TournamentDetail::where('tournament_id', "=", $id)->delete();

    TournamentLog::create([
      'tournament_id' => $tournament->id,
      'room_id' => $tournament->room_id,
      'user_id' => $request->user()->id,
      'type' => 5,
      'changes' => []
    ]);

    $tournament->delete();

    return $this->jsonResponseSuccess(
      trans('tournament/tournament.delete.success')
    );
  }

  /**
   * Remove all finished tournaments.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws Throwable
   */
  public function destroyFinished(Request $request): JsonResponse
  {
    try {
      $password = $request->input('password');
      $user = $request->user();
      $userId = $user->id;

      if (!$user || !Hash::check($password, $user->password)) {
        return $this->jsonResponseFail([
          'message' => trans('Invalid password.'),
        ]);
      }

      $room = Room::where('user_id', '=', $userId)->first();
      $finishedTournamentIds = Tournament::with('detail')
        ->where('room_id', '=', $room->id)
        ->whereHas('detail', function ($q) {
          $q->where('startday', '<', Carbon::now()->setHour(8)->setMinute(0));
        })
        ->get()->pluck('id');

      DB::beginTransaction();

      TournamentDetail::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentDescription::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentRebuyCount::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentStructure::whereIn('tournament_id', $finishedTournamentIds)->delete();

      TournamentCheckinPlayer::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentLateUser::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentRegistrationLog::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentRegisterPlayer::whereIn('tournament_id', $finishedTournamentIds)->delete();
      TournamentWaitingPlayer::whereIn('tournament_id', $finishedTournamentIds)->delete();

      $logs = [];

      foreach ($finishedTournamentIds as $id) {
        $logs[] = [
          'tournament_id' => $id,
          'room_id' => $room->id,
          'user_id' => $userId,
          'type' => 5,
          'changes' => "[]",
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ];
      }
      DB::table('tournament_logs')->insert($logs);

      Tournament::whereIn('id', $finishedTournamentIds)->delete();
      DB::commit();

      return $this->jsonResponseSuccess([
        'message' => trans('tournament/tournament.delete.success'),
      ]);
    } catch (Throwable $e) {
      DB::rollBack();
      throw $e;
    }
  }

  /**
   * Duplicate the specified resource in storage.
   *
   * @param TournamentDuplicateRequest $request
   * @return JsonResponse
   */
  public function duplicate(TournamentDuplicateRequest $request): JsonResponse
  {
    $data = $request->validated();
    $originalItem = $this->tournament->show(['id' => $data['tournament']['original_id']]);

    $newItem = [
      'title' => $data['tournament']['title'],
      'user_id' => $request->user()->id,
      'room_id' => $originalItem->room_id,
      'status' => 0, // non-publish
      'archived' => $originalItem->archived,
      'closed' => $originalItem->closed,
      'is_freeze' => $originalItem->is_freeze,
    ];

    $newItem['slug'] = $this->tournament->generateSlug($data['tournament']['title'], $data['detail']['startday']);

    $detail = Arr::except($originalItem->detail->toArray(), ['id', 'tournament_id', 'created_at', 'updated_at']);
    $detail['startday'] = $data['detail']['startday'];
    $detail['lastday'] = $data['detail']['lastday'];
    $detail['bounusdeadline'] = $data['detail']['bounusdeadline'];

    $descriptions = $originalItem->description->toArray();
    $structures = $originalItem->structure->toArray();

    if (!$this->room->isOwner($request->user(), $newItem['room_id'])) {
      return $this->jsonResponseFail(
        trans('user/tournament/tournament.duplicate.fail')
      );
    }

    if (!empty($detail['lastday']) && $detail['startday'] > $detail['lastday']) {
      return $this->jsonResponseFail(
        trans('End of tournament cannot be before the start date!!'),
        200
      );
    }

    $roomDetails = Room::find($newItem['room_id']);

    $buyin = intval($detail['buyin']);
    $bounty = intval($detail['bounty']);
    $maxreentries = intval($detail['maxreentries']);
    $reentry = intval($detail['reentry']);
    $reentryBounty = intval($detail['reentry_bounty']);
    $reentrySum = $reentry + $reentryBounty;
    $limit = $buyin + $bounty + ($maxreentries * $reentrySum);
    $limitWithoutReEntry = $buyin + $bounty;

    if ($roomDetails->buyuinlimit < $limit || $roomDetails->buy_in_limit_without_reentry < $limitWithoutReEntry) {
      return $this->jsonResponseFail(
        trans("Can't add tournament.Limit of buy-in reached!!!"),
        200
      );
    }

    //create a new tournament from original
    $tournament = $this->tournament->create($newItem);

    // ========= create ========= //
    if ($tournament) {
      $tournament->detail()->create($detail);
      /* updateOrCreate description */
      foreach ($descriptions as $key => $item) {
        $tournament->description()->updateOrCreate(
          ['language' => $item['language']],
          ['language' => $item['language'], 'description' => $item['description']]
        );
      }

      /* updateOrCreate Structure */
      if (!empty($structures)) {
        foreach ($structures as $key => $item) {
          $tournament->structure()->updateOrCreate(
            ['order' => $item['order']],
            ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
          );
        }
      }

      sendEmail(
        'admin',
        'new_tournament',
        null,
        [
          'room_title' => $roomDetails->title,
          'tournament_title' => $tournament->title,
          'tournament_date' => (new Carbon($data['detail']['startday']))->format('d.m.Y'),
        ]
      );

      return $this->jsonResponseSuccess(
        trans('tournament/tournament.duplicate.success')
      );
    }

    return $this->jsonResponseFail(
      trans('tournament/tournament.duplicate.fail'),
      400
    );
  }

  public function updatestatus(TournamentUpdateStatusRequest $request): JsonResponse
  {
    $data = $request->validated();
    $tournament = Tournament::find($data['id']);

    if ($data['status'] === 0) {
      $data['disable_registration'] = false;
    }

    $tournament->update($data);

    TournamentLog::create([
      'tournament_id' => $tournament->id,
      'room_id' => $tournament->room_id,
      'user_id' => $request->user()->id,
      'type' => $tournament->status == 1 ? 3 : 4, // 3: published, 4: undo published
      'changes' => []
    ]);

    return $this->jsonResponseSuccess(
      trans('tournament/tournament.delete.success')
    );
  }

  public function updatefreezestatus(TournamentUpdateStatusRequest $request): JsonResponse
  {
    $data = $request->validated();
    $tournament = $this->tournament->update(
      $data['id'],
      ['is_freeze' => $data['status']]
    );
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.delete.success')
    );
  }

  public function set_premium(request $request): JsonResponse
  {
    $tournament = Tournament::find($request->id);
    $tournament->update(
      [
        'ispremium' => $request->ispremium,
        'startdate' => $request->startdate,
        'enddate' => $request->enddate,
        'location' => $request->location,
      ]
    );
    /*$data =  $request->validated();
    $tournament = $this->tournment->update(
        $data['id'],
        ['status'=>$data['status']]
    );*/
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.success')
    );
  }

  public function load_template($id): bool|string
  {

    $templates = DB::table('templates')
      ->select('template_structures.*')
      ->join('template_structures', 'template_structures.template_id', '=', 'templates.id')->where('templates.id', "=", $id)->get();
    $response = array(
      "total" => count($templates),
      "data" => $templates
    );
    return json_encode($response);
  }

  public function templates(request $request): bool|string
  {
    //get room id
    $user = $request->user();
    $room_id = $user->room->id;
    $templates = Template::with('structure')->where('room_id', "=", $room_id)->get();
    $response = array(
      "total" => count($templates),
      "data" => $templates
    );
    return json_encode($response);
  }

  public function createTemplate(request $request): JsonResponse
  {
    //get room id
    $user = $request->user();
    $roomId = $user->room->id;
    $template = Template::create(
      ['title' => $request->title, 'room_id' => $roomId]
    );
    foreach ($request->structure as $key => $item) {
      $template->structure()->updateOrCreate(
        ['order' => $item['order']],
        ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
      );
    }
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.success')
    );
  }

  public function updateTemplate(request $request, $id): JsonResponse
  {
    $template = Template::find($id);
    $template->title = $request->title;
    $template->save();

    foreach ($request->structure as $key => $item) {
      $template->structure()->updateOrCreate(
        ['order' => $item['order']],
        ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
      );
    }
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.success')
    );
  }

  public function deleteTemplates(Request $request, $id): JsonResponse
  {
    $template = Template::with('structure')->find($id);
    $template->structure()->delete();
    $template->delete();
    return $this->jsonResponseSuccess(['id' => $id]);
  }

  public function updatetournamentstatus($id): JsonResponse
  {
    $tournament = Tournament::find($id);
    if (!$tournament)
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.not.found.success')
      );
    if ($tournament->status == 1) $status = 2;
    else $status = 1;
    $tournament->status = $status;
    $tournament->save();
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.status.success')
    );
  }

  public function archivetournament($id): JsonResponse
  {
    $tournament = Tournament::find($id);
    if (!$tournament)
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.not.found.success')
      );
    $tournament->archived = 1;
    $tournament->status = 2;
    $tournament->save();
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.archive.status.success')
    );
  }

  public function sendEmail(request $request): JsonResponse
  {
    $tournament = Tournament::find($request['tournament_id']);

    if (empty($tournament)) {
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.mail.fail')
      );
    }

    $emailSetting = getEmailSetting();
    if ($emailSetting['email_setting'] === 'block') {
      return $this->jsonResponseSuccess([
        'message' => 'Sending email does not allowed.'
      ]);
    }

    $room = $tournament->room;
    $replyAddress = $room->detail->contact;
    $replyName = $room->title;
    $emails = $tournament->registeredPlayers->pluck('email')->merge($tournament->waitingPlayers->pluck('email'));
    $subject = $request['subject'];
    $content = $request['content'];

    foreach ($emails as $email) {
      Mail::send([], [], function ($m) use ($email, $replyAddress, $replyName, $subject, $content) {
        $m->to($email)->replyTo($replyAddress, $replyName)->subject($subject)->text($content);
      });
    }

    TournamentLog::create([
      'tournament_id' => $tournament->id,
      'room_id' => $tournament->room_id,
      'user_id' => $request->user()->id,
      'type' => 6,
      'changes' => []
    ]);

    return $this->jsonResponseSuccess([
      'message' => 'Sent.',
      'emails' => $emails,
    ]);
  }

  public function exportcsv($id): bool|JsonResponse|string
  {

    $csvFileName = time() . '.csv';
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
    ];

    $tournament = Tournament::find($id);
    if ($tournament) {
      $roomID = $tournament->room_id;
      $useremails = DB::table('room_users')
        ->select('users.*')
        ->join('users', 'users.id', '=', 'room_users.user_id')->where('room_id', "=", $roomID)->get();
      $handle = fopen($csvFileName, 'w');
      fputcsv($handle, ['id', 'email']); // Add more headers as needed

      foreach ($useremails as $useremail) {
        fputcsv($handle, [$useremail->id, $useremail->email]); // Add more fields as needed
      }

      fclose($handle);
      $response = array(
        'file' => $csvFileName,
        'status' => 'File Export Success!!!'
      );
      return json_encode($response);
    }
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.not.found')
    );
  }

  public function getRoom(Request $request): RoomResourceCollection
  {

    return RoomResourceCollection::make(
      $this->room->all(null, ['user_id' => $request->user()->id], null, null, null, null, 100, true)
    );
  }

  public function getLaterByRoom(Request $request): bool|string
  {
    $tournaments = Tournament::all();
    if ($tournaments) {
      $room_id = array();
      foreach ($tournaments as $tournament) {
        if (!in_array($tournament->room_id, $room_id)) {
          $room_id[] = $tournament->room_id;
        }
      }
      $roomid = $request->user()->room->id;
      for ($i = 0; $i < count($room_id); $i++) {
        if ($room_id[$i] == $roomid) {
          $lateUsers = DB::table('tournament_late_users')
            ->select('tournaments.title', 'users.email', 'tournament_late_users.*', 'user_profiles.firstname', 'user_profiles.lastname')
            ->join('tournaments', 'tournaments.id', '=', 'tournament_late_users.tournament_id')
            ->join('users', 'users.id', '=', 'tournament_late_users.user_id')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->where('tournaments.room_id', "=", $roomid)->get();
          if ($lateUsers) {
            $response = array(
              'status' => true,
              'data' => $lateUsers
            );
            return json_encode($response);
          }
        }
      }
    }
    $response = array(
      'status' => true,
      'data' => []
    );
    return json_encode($response);
  }

  public function destroyByRoom(Request $request, $id): bool|string
  {
    $lateuser = TournamentLateUser::find($id)->delete();
    $response = array(
      'message' => 'late Arraival Deleted !!',
      'status' => true
    );
    return json_encode($response);
  }

  public function getLateArrivalById(Request $request, $id): bool|string
  {

    $lateuser = TournamentLateUser::find($id);
    if ($lateuser) {
      $response = array(
        'status' => true,
        'data' => $lateuser
      );
      return json_encode($response);
    }

    $response = array(
      'status' => true,
      'data' => []
    );
    return json_encode($response);
  }

  public function updateByRoom(Request $request, $id): bool|string
  {
    $late = TournamentLateUser::find($id);
    $late->latetime = $request->latetime;
    $late->save();
    $response = array(
      'message' => 'late Arraival Updated !!',
      'status' => true
    );
    return json_encode($response);
  }

  public function cuurentanonymous($slug, $id): bool|string
  {
    $result = Tournament::where('slug', "=", $slug)->where('user_id', "=", $id)->count();
    if ($result > 0) {
      $response = array(
        'is_found' => 1,
        'status' => true
      );
    } else {
      $response = array(
        'is_found' => 0,
        'status' => true
      );
    }
    return json_encode($response);
  }
}
