<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Services\User\UserService;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Requests\User\Room\RoomCreateRequest;
use App\Http\Requests\User\Room\RoomUpdateRequest;
use App\Http\Resources\User\Room\RoomResource;
use App\Models\Room\Room;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;

use App\Http\Resources\User\UserResourceCollection;

class PlayerController extends Controller
{
    use ResponseTrait;
    /**
     * @var RoomService
     */
    protected RoomService $room;
    protected UserService $user;


    /**
     * @param RoomService $tournment
     */
    public function __construct(RoomService $room,UserService $user)

    {
        $this->room = $room;
        $this->user = $user;
    }
    /**
     * @param RoomResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $allusers=[];
        foreach($request->user()->room as $room){
            foreach($room->room_users as $user){
                $allusers[]=$user->user_id;  
            }
        }
        $allusers = array_unique($allusers, SORT_REGULAR);
        //list all players of rooms
        return UserResourceCollection::make(
            $this->user->getUserByIds($allusers)
        );
        //get manual users and room users

    }
    
}