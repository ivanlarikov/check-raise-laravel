<?php

namespace App\Http\Controllers\User\Tournament\Log;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;

use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentLog;
use App\Traits\Response\ResponseTrait;
use App\Models\Room\Room;

use App\Http\Resources\Tournament\Log\TournamentLogResourceCollection;

use Illuminate\Http\Request;

class LogController extends Controller
{
    use ResponseTrait;   

    /**
     * @param TournamentService $tournment
     */
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $roomIds=$request->user()->room()->pluck('id')->toArray();
        $tournaments=Tournament::whereIn('room_id',$roomIds)->get()->pluck('id')->toArray();
        $tournament_logs=TournamentLog::whereIn('tournament_id',$tournaments)->get();
        return TournamentLogResourceCollection::make(
            $tournament_logs
        );
    }

    
}
