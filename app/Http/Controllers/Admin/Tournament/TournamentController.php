<?php

namespace App\Http\Controllers\Admin\Tournament;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;
use App\Services\Room\RoomService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Requests\Admin\Tournament\TournamentCreateRequest;
use App\Http\Requests\Admin\Tournament\TournamentUpdateStatusRequest;
use App\Http\Requests\Admin\Tournament\TournamentUpdateRequest;
use App\Models\Tournament\TournamentDescription;

use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;

use App\Models\Room\Template\Template;
use App\Models\Room\Template\TemplateStructure;
use App\Models\Room\Room;
use App\Http\Requests\User\Registration\PlayerRegistrationRequest;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Resources\Tournament\TournamentDetailResource;
use App\Http\Resources\User\Room\RoomStatisticsResource;
use App\Models\Tournament\TournamentLateUser;
use Illuminate\Support\Str;

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
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $query = Tournament::query();
    $query->select('id', 'room_id', 'title', 'status', 'slug')
      ->without('description')
      ->has('detail')
      ->with(['detail' => function ($query) {
        $query->select('tournament_id', 'startday', 'buyin', 'bounty', 'maxreentries', 'rake', 'reservedplayers', 'maxplayers');
      }])
      ->with(['room' => function ($query) {
        $query->select('id', 'title')
          ->without('detail');
      }])
      ->withCount('registeredPlayers');
    $query = $query->groupBy('id');

    $data = $query->get();
    $total = $data->count();

    return $this->jsonResponseSuccess([
      'data' => $data,
      'total' => $total,
    ]);
  }
  public function store(TournamentCreateRequest $request): JsonResponse
  {
    $data =  $request->validated();
    /*if(!$this->room->isOwner($request->user(),$data['tournament']['room_id']))
            return $this->jsonResponseFail(
                trans('user/tournament/tournament.create.fail')
            );*/
    //print_r($data);die;
    //set the user id
    $startday = $data['details']['startday'];
    $lastday = $data['details']['lastday'];
    if (!empty($lastday) && $startday > $lastday) {
      return $this->jsonResponseFail(
        trans('End of tournament cannot be before the start date!!'),
        200
      );
    }

    $slug = Str::of($data['tournament']['title'])->slug('-');
    $slug .= date('-d-m-Y', strtotime($data['details']['startday']));
    $data['tournament']['slug'] = $slug;
    $data['tournament']['user_id'] = $request->user()->id;
    $data['tournament']['status'] = 0;
    //create tournament
    $tournament = $this->tournment->create(
      $data['tournament']
    );
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
      if (!empty($data['structure'])) {
        foreach ($data['structure'] as $key => $item) {
          $tournament->structure()->updateOrCreate(
            ['order' => $item['order']],
            ['order' => $item['order'], 'sb' => $item['sb'], 'bb' => $item['bb'], 'ante' => $item['ante'], 'duration' => $item['duration'], 'isbreak' => $item['isbreak'], 'breaktitle' => $item['breaktitle']]
          );
        }
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
   * @param  int  $id
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

    return $this->jsonResponseFail(
      trans('tournament/tournament/show.create.fail')
    );
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(TournamentUpdateRequest $request): JsonResponse
  {
    $data =  $request->validated();
    /*if(!$this->room->isOwner($request->user(),$data['tournament']['room_id']))
            return $this->jsonResponseFail(
                trans('user/tournament/tournament.create.fail')
            );*/
    //check if tournament is in the room later
    //
    //set the user id

    $startday = $data['details']['startday'];
    $lastday = $data['details']['lastday'];
    if (!empty($lastday)  && $startday > $lastday) {
      return $this->jsonResponseFail(
        trans('End of tournament cannot be before the start date!!'),
        200
      );
    }

    $room = Room::find($data['tournament']['room_id']);
    if($room->status != 1 || $startday >= $room->expiry) {
      $data['tournament']['status'] = 0;
    }

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
      if($tournament->status != 1) {
        $slug = Str::of($data['tournament']['title'])->slug('-');
        $slug .= date('-d-m-Y', strtotime($data['details']['startday']));
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
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
    $tournament = Tournament::where('id', "=", $id)->delete();
    $tournament = TournamentDescription::where('tournament_id', "=", $id)->delete();
    $tournament = TournamentDetail::where('tournament_id', "=", $id)->delete();
    //if ($tournament){
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.delete.success')
    );
    //}
    // return $this->jsonResponseFail(
    //     trans('tournament/tournament.delete.fail')
    // );
  }

  public function updatestatus(TournamentUpdateStatusRequest $request)
  {
    $data =  $request->validated();
    $tournament = $this->tournment->update(
      $data['id'],
      ['status' => $data['status']]
    );
    return $this->jsonResponseSuccess(
      trans('tournament/tournament.update.success')
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
      trans('tournament/tournament.update.success')
    );
  }

  public function save_template(request $request)
  {
    //get room id
    //$user=$request->user();
    $room_id = $request->room_id;
    $template = Template::create(
      ['title' => $request->title, 'room_id' => $room_id]
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
    $room_id = $user->room->id;
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
    if ($tournament->status == 1) $status = 2;
    else $status = 1;
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
  public function sendemail(request $request)
  {
    $tournament = Tournament::find($request['tournament_id']);
    if ($tournament) {
      $useremails = DB::table('room_users')
        ->select('users.email')
        ->join('tournaments', 'tournaments.room_id', '=', 'room_users.room_id')
        ->join('users', 'users.id', '=', 'room_users.user_id')->where('tournaments.id', $request['tournament_id'])->distinct()->get();
      //echo "<pre>";print_r($useremails);die;
      foreach ($useremails as $useremail) {
        //mail($useremail->email,$request['subject'],$request['content']);
      }
      return $this->jsonResponseSuccess(
        trans('tournament/tournament.mail.success')
      );
    }
    return $this->jsonResponseFail(
      trans('tournament/tournament.mail.fail')
    );
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
      $useremails = DB::table('room_users')
        ->select('users.*')
        ->join('tournaments', 'tournaments.room_id', '=', 'room_users.room_id')
        ->join('users', 'users.id', '=', 'room_users.user_id')->where('tournaments.id', $id)->distinct()->get();
      /*$roomID=$tournament->room_id;

            $useremails=DB::table('room_users')
            ->select('users.*')
            ->join('users', 'users.id', '=', 'room_users.user_id')->where('room_id', "=", $roomID)->get();*/
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
  public function getAllRoom(Request $request)
  {
    $templates = Template::All();
    $response = array(
      "total" => count($templates),
      "data" => $templates
    );
    return json_encode($response);
  }
  public function getTemplates(request $request)
  {
    $templates = Template::All();
    $response = array(
      "total" => count($templates),
      "data" => $templates
    );
    return json_encode($response);
  }
  public function getStatisticsByRoomId(Request $request, $id): RoomStatisticsResource
  {
    return RoomStatisticsResource::make(
      $room = $this->room->show(
        ['id' => $id]
      )
    );
  }
  public function getTournamentListByRoomId(Request $request, $id): TournamentResourceCollection
  {
    return TournamentResourceCollection::make(
      $this->tournment->all(null, ['room_id' => $id], null, null, null, null, null, true)
    );
  }
  public function getLateArrival(Request $request)
  {

    $lateUsers = DB::table('tournament_late_users')
      ->select('tournaments.title', 'users.email', 'tournament_late_users.*', 'user_profiles.firstname', 'user_profiles.lastname')
      ->join('tournaments', 'tournaments.id', '=', 'tournament_late_users.tournament_id')
      ->join('users', 'users.id', '=', 'tournament_late_users.user_id')
      ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')->get();
    if ($lateUsers) {
      $response = array(
        'status' => true,
        'data' => $lateUsers
      );
      return json_encode($response);
    }

    $response = array(
      'status' => true,
      'data' => []
    );
    return json_encode($response);
  }
  public function getLateArrivalById(Request $request, $id)
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
  public function destroyLateArrival(Request $request, $id)
  {
    $lateuser = TournamentLateUser::find($id)->delete();
    $response = array(
      'message' => 'late Arraival Deleted !!',
      'status' => true
    );
    return json_encode($response);
  }
  public function updateLateArrival(Request $request, $id)
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
}
