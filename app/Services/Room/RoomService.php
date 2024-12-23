<?php

namespace App\Services\Room;

use App\Repositories\Room\RoomRepository;
use App\Services\BaseService;
use App\Models\Room\Room;
use App\Models\User\User;

class RoomService extends BaseService
{
    /**
     * @var RoomRepository
     */
    protected RoomRepository $room;

    /**
     * @param RoomRepository $tournament
     */
    public function __construct(RoomRepository $room)
    {
        $this->room = $room;
        parent::__construct($this->room);
    }

    public function isOwner(User $user,$roomId)
    {
        return $user->room()->where('id',$roomId)->exists();
    }
	/*public function getTournaments(Tournament $tournament){
		echo "<pre>";
		print_r($user->vendors()->get());
		die;
	}*/
}
