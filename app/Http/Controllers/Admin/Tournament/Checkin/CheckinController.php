<?php

namespace App\Http\Controllers\User\Tournament\Checkin;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;
use App\Services\Room\RoomService;
use App\Services\User\UserService;

use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Resources\User\UserResourceCollection;

use App\Http\Requests\User\Tournament\Checkin\RegisterPlayerRequest;
use App\Http\Requests\User\Tournament\Checkin\CheckinCountRequest;
use App\Http\Requests\User\Tournament\Checkin\GetPlayerRequest;
use App\Http\Resources\User\Tournament\Checkin\CheckinResource;

use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;

use Illuminate\Http\Request;
class CheckinController extends Controller
{
    use ResponseTrait;
    /**
     * @var TournamentService
     */
    protected TournamentService $tournment;
    protected RoomService $room;
    protected UserService $user;

    /**
     * @param TournamentService $tournment
     */
    public function __construct(TournamentService $tournment,RoomService $room,UserService $user)
    {
        $this->tournment = $tournment;
        $this->room = $room;
        $this->user = $user;
    }

    public function index($id)
    {
        return CheckinResource::make(
            $this->tournment->show(
                ['id' => $id]
            )
        ); 
      
    }

    public function register(Request $request)
    {
        $this->tournment->registerPlayer($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }

    public function deregister(Request $request)
    {
        $this->tournment->deregisterPlayer($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
      
    }

    public function users(GetPlayerRequest $request): UserResourceCollection
    {
        $data =  $request->validated();
        // get users
        return UserResourceCollection::make(
            $users=$this->user->getUserByRole('Player',$data['keyword'])
        );        
    }

    public function checkin(Request $request)
    {
        $this->tournment->checkinPlayer($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }

    public function cancelcheckin(Request $request)
    {
        $this->tournment->cancelcheckinPlayer($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }

    //plusrebuy
    public function plusrebuy(Request $request)
    {
        $this->tournment->plusrebuy($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }

    //minusrebuy
    public function minusrebuy(Request $request)
    {
        $this->tournment->minusrebuy($request->id,$request->user_id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }

    public function updatecounts(CheckinCountRequest $request)
    {
        $data =  $request->validated();
        $this->tournment->updateCounts($data['tournament_id'],$data['maxplayers'],$data['reservedplayers']);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }
    public function checkout(Request $request)
    {
        $this->tournment->checkout($request->id);
        return $this->jsonResponseSuccess(
            trans('admin/room/room.create.success')
        );
    }
}
