<?php

namespace App\Repositories\Room;

use App\Models\Room\Room;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class RoomRepository extends BaseRepository
{
    /**
     * @var Room
     */
    protected Room $room;

    /**
     * @param Room $room
     */
    public function __construct(Room $room)
    {
        $this->room = $room;
        parent::__construct($room);
    }
    
}
