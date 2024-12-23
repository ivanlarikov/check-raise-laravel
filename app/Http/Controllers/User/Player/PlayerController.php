<?php

namespace App\Http\Controllers\User\Player;

use App\Http\Controllers\Controller;
use App\Services\User\Player\PlayerService;
use App\Services\Room\RoomService;
/*use App\Http\Resources\User\Player\PlayerResourceCollection;
use App\Http\Resources\User\Player\PlayerResource;*/

use App\Http\Resources\User\Player\PlayerResourceCollection;
use App\Http\Resources\User\Player\PlayerResource;
/*use App\Http\Requests\User\Player\PlayerCreateRequest;
use App\Http\Requests\User\Player\PlayerUpdateStatusRequest;
use App\Http\Requests\User\Player\PlayerUpdateRequest;
use App\Models\Player\PlayerDescription;*/
use Illuminate\Support\Facades\Hash;
use App\Models\Player\PlayerDetail;
use App\Models\Player\Player;

use App\Models\Room\Template\Template;
use App\Models\Room\Template\TemplateStructure;

use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    use ResponseTrait;
    /**
     * @var PlayerService
     */
    protected PlayerService $player;
    protected RoomService $room;
    
    /**
     * @param PlayerService $player
     */
    public function __construct(PlayerService $player,RoomService $room)
    {
        $this->player = $player;
        $this->room = $room;
    }

    /**
     * @return PlayerResourceCollection
     */
    public function index(Request $request): PlayerResource
    {
        return PlayerResource::make(
            $this->room->show(
                ['user_id'=>$request->user()->id]
            )
        );
    }
}
