<?php

namespace App\Http\Controllers\User\Director;

use App\Http\Controllers\Controller;
use App\Models\Tournament\TournamentLog;
use App\Services\Tournament\TournamentService;
use App\Services\Room\RoomService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Requests\User\Tournament\TournamentCreateRequest;
use App\Http\Requests\User\Tournament\TournamentUpdateStatusRequest;
use App\Http\Requests\User\Tournament\TournamentUpdateRequest;
use App\Models\Tournament\TournamentDescription;
use App\Http\Resources\Tournament\TournamentDetailResource;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Models\Room\Template\Template;
use App\Models\Room\Template\TemplateStructure;

use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class TournamentController extends Controller
{
  use ResponseTrait;

  /**
   * @var TournamentService
   */
  protected TournamentService $tournment;
  protected RoomService $room;

  /**
   * @param TournamentService $tournment
   */
  public function __construct(TournamentService $tournment, RoomService $room)
  {
    $this->tournment = $tournment;
    $this->room = $room;
  }

  /**
   * @return TournamentResourceCollection
   */
  public function index(Request $request): TournamentResourceCollection
  {
    //check if rooms are set
    //Get tournament IDs
    $id = $request->user()->director_room->room_id;
    $tournaments = DB::table('tournaments')
      ->select('tournaments.id')
      ->join('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
      ->join('rooms', 'rooms.id', '=', 'tournaments.room_id')->where('tournaments.archived', 0);
    $tournaments = $tournaments->where('rooms.id', $id)->orderBy('tournaments.id', 'desc')
      ->get()->pluck('id')->toArray();

    /*$tournaments=$tournaments->where(['tournaments.status'=>1,'tournaments.closed'=>0,'tournaments.archived'=>0])
        ->orderBy('tournaments.id', 'desc')
        ->get()->pluck('id')->toArray();*/
    return TournamentResourceCollection::make(
      Tournament::whereIn('id', $tournaments)->get()
    );
  }

  public function store(TournamentCreateRequest $request): \Illuminate\Http\JsonResponse
  {
    $data = $request->validated();
    /*if(!$this->room->isOwner($request->user(),$data['tournament']['room_id']))
        return $this->jsonResponseFail(
            trans('user/tournament/tournament.create.fail')
        );*/
    //set the user id
    $data['tournament']['user_id'] = $request->user()->id;
    //create tournament
    $tournament = $this->tournment->create(
      $data['tournament']
    );
    //echo "<pre>";print_r($tournament);die;
    if ($tournament) {
      //$tournament->description()->create($data);
      $tournament->detail()->create($data['details']);

      /* updateOrCreate description */
      foreach ($data['descriptions'] as $key => $item) {
        $tournament->description()->updateOrCreate(
          ['language' => $item['language']],
          ['language' => $item['language'], 'description' => $item['description']]
        );
      }

      /* updateOrCreate Structure */
      foreach ($data['structure'] as $key => $item) {
        $tournament->structure()->updateOrCreate(
          ['order' => $item['order']],
          ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
        );
      }


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
   * @return \Illuminate\Http\Response
   */
  public function show(Request $request, $id): TournamentDetailResource
  {
    $data = $this->tournment->show(
      ['slug' => $id]
    );
    if ($data) {
      return new TournamentDetailResource(
        $data
      );
    }

    return new TournamentDetailResource(
      trans('tournament/tournament/show.create.fail')
    );
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function update(TournamentUpdateRequest $request): \Illuminate\Http\JsonResponse
  {
    $data = $request->validated();
    /* if(!$this->room->isOwner($request->user(),$data['tournament']['room_id']))
         return $this->jsonResponseFail(
             trans('user/tournament/tournament.create.fail')
         );*/
    //check if tournament is in the room later
    //
    //set the user id

    $data['tournament']['user_id'] = $request->user()->id;
    $tournament = $this->tournment->update(
      $data['tournament']['id'],
      $data['tournament']
    );
    /* update details */
    $tournament = $this->tournment->show(
      ['id' => $data['tournament']['id']]
    );

    if ($tournament) {
      //$tournament->description()->create($data);
      $tournament->detail->update($data['details']);

      /* updateOrCreate description */
      //echo "<pre>";print_r($data);die;
      if (!empty($data['descriptions'][0]['description'])) {
        foreach ($data['descriptions'] as $key => $item) {
          $tournament->description()->updateOrCreate(
            ['language' => $item['language']],
            ['language' => $item['language'], 'description' => $item['description']]
          );
        }
      }

      $tournament->structure()->delete();
      /* updateOrCreate Structure */
      foreach ($data['structure'] as $key => $item) {
        $tournament->structure()->updateOrCreate(
          ['order' => $item['order']],
          ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
        );
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
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //echo $id;die;
    $tournament = Tournament::where('id', "=", $id)->delete();
    $tournament = TournamentDescription::where('tournament_id', "=", $id)->delete();
    $tournament = TournamentDetail::where('tournament_id', "=", $id)->delete();
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.delete.success')
    );
    /*if ($tournament)
        return $this->jsonResponseSuccess(
            trans('tournament/tournament.delete.success')
        );
    return $this->jsonResponseFail(
        trans('tournament/tournament.delete.fail')
    );*/
  }

  public function updatestatus(TournamentUpdateStatusRequest $request)
  {
    $data = $request->validated();
    $tournament = $this->tournment->update(
      $data['id'],
      ['status' => $data['status']]
    );

    return $this->jsonResponseSuccess(
      trans('tournament/tournament.updatestatus.success')
    );
  }

  public function set_premium(request $request)
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
      trans('tournament/tournament.load.success')
    );
  }

  public function save_template(request $request)
  {
    //get room id
    $user = $request->user();
    //$getroom=DB::table('room_users')->where('user_id', "=", $user->id)->first();
    //$room_id=2909;
    //$room_id=$user->room->id;
    $template = Template::create(
      ['title' => $request->title, 'room_id' => $request->room_id]
    );
    foreach ($request->structure as $key => $item) {
      $template->structure()->updateOrCreate(
        ['order' => $item['order']],
        ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
      );
    }
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.save_template.success')
    );
  }

  public function load_template($id)
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

  public function templates(request $request)
  {
    //get room id
    $user = $request->user();
    //$room_id=$user->room->id;
    $room = DB::table('room_users')->where('user_id', "=", $user->id)->first();
    $room_id = $room->room_id;
    if (empty($room_id)) {
      $room_id = 2909;
    }
    $templates = Template::where('room_id', "=", $room_id)->get();
    $response = array(
      "total" => count($templates),
      "data" => $templates
    );
    return json_encode($response);
  }

  public function updatetournamentstatus($id)
  {
    $tournament = Tournament::find($id);
    if (!$tournament)
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.not.found.success')
      );
    if ($tournament->status == 1) $status = 2; else $status = 1;
    $tournament->status = $status;
    $tournament->save();
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.status.success')
    );

  }

  public function archivetournament($id)
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

  public function sendEmail(request $request)
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
        $m->to($email)->replyTo($replyAddress, $replyName)->subject($subject)->html($content);
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

  public function exportcsv($id)
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

  public function getRoom(Request $request)
  {
    $user_id = $request->user()->id;
    $getroom = DB::table('room_users')
      ->select('rooms.id', 'rooms.title', 'rooms.slug', 'rooms.user_id')
      ->join('rooms', 'rooms.id', '=', 'room_users.room_id')->where('room_users.user_id', "=", $user_id)->first();
    if ($getroom) {
      $response = array(
        'status' => true,
        'data' => $getroom
      );
      return json_encode($response);
    }
    $response = array(
      'status' => false,
      'data' => ''
    );
    return json_encode($response);

    /*return RoomResourceCollection::make(
        $this->room->all(null, ['user_id' => $request->user()->id], null, null, null, null, 100, true)
    );*/
  }
}
