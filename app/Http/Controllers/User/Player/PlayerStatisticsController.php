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

use App\Models\Player\PlayerDetail;
use App\Models\Player\Player;

use App\Models\User\User;
use App\Models\User\UserProfile;

use App\Models\Room\Template\Template;
use App\Models\Room\Template\TemplateStructure;
use App\Models\Room\RoomMember;

use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlayerStatisticsController extends Controller
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
    public function __construct(PlayerService $player, RoomService $room)
    {
        $this->player = $player;
        $this->room = $room;
    }

    /**
     * @return PlayerResourceCollection
     */
    public function index($id)
    {
        $profile = DB::table('users')
            ->select('users.email', 'user_profiles.*')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')->where('users.id', $id)->first();

        $checkin_data = DB::table('tournament_checkin_players')->where('user_id', $id);
        $checkin_player = DB::table('tournament_checkin_players')->select('created_at')->where('user_id', $id)->orderBy('id', 'desc')->first();
        if ($checkin_data) {
            $registration_last_week = $checkin_data->whereBetween(
                'created_at',
                [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]
            )->count();
            $registration_last_month = $checkin_data->whereBetween(
                'created_at',
                [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]
            )->count();
            $registration_last_six_month = $checkin_data->whereBetween(
                'created_at',
                [Carbon::now()->subMonth(6), Carbon::now()]
            )->count();
            $registration_last_twelve_month = $checkin_data->whereBetween(
                'created_at',
                [Carbon::now()->subMonth(12), Carbon::now()]
            )->count();

            $buyin_bounties = DB::table('tournaments')
                ->select('tournament_details.startday', 'tournament_details.buyin', 'tournament_details.bounty')
                ->join('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
                ->join('tournament_checkin_players', 'tournament_checkin_players.tournament_id', '=', 'tournaments.id')
                ->where('tournament_checkin_players.user_id', $id)->get();
            $cnt = count(json_decode(json_encode($checkin_data), true));
            $without_checkin_data_response = array(
                'last_registration_without_check_in_date' => $checkin_player,
                'number_of_registrations_without_check_in' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                )
            );
            $checkin_data_response = array(
                'last_registration_with_check_in_date' => $checkin_player,
                'number_of_registrations_with_check_in' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                ),
                'average_buy_in_bount' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                ),
                'number_of_re_entries' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                ),
                'cumulated_buy_in_bounty_re_entries' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                ),
                'cumulated_rakes' => array(
                    'last_week' => $registration_last_week,
                    'last_month' => $registration_last_month,
                    'last_six_month' => $registration_last_six_month,
                    'twelve_month' => $registration_last_twelve_month,
                    'ever' => $cnt
                ),
            );
        }

        $roomMembers = DB::table('rooms')
            ->select('rooms.id as room_id', 'room_members.id as room_member_id', 'rooms.title', 'room_members.expiry')
            ->join('room_members', 'room_members.room_id', '=', 'rooms.id')->where('room_members.user_id', $id)->get();

        $rooms = DB::table('rooms')
            ->select(
                'rooms.id as room_id',
                'rooms.title',
                'room_users.created_at as first_registration_date',
                'room_settings.is_membership as is_membership'
            )
            ->leftJoin('room_users', 'room_users.room_id', '=', 'rooms.id')
            ->leftJoin('room_settings', 'room_settings.room_id', '=', 'rooms.id')
            ->where('room_users.user_id', '=', $id)
            ->get();

        $response = array(
            'player_info' => $profile,
            'statistics_without_checkin' => array($without_checkin_data_response),
            'statistics_with_checkin' => array($checkin_data_response),
            'tournament' => '',
            'room_memeber' => $roomMembers,
            'rooms' => $rooms,
        );
        return $response;
        /*return PlayerResource::make(
            $this->room->show(
                ['user_id'=>$request->user()->id]
            )
        );*/
    }
}
