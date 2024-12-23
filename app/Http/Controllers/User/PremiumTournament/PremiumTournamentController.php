<?php

namespace App\Http\Controllers\User\PremiumTournament;

use App\Http\Controllers\Controller;
use App\Services\User\Player\PlayerService;
use App\Services\Room\RoomService;
/*use App\Http\Resources\User\Player\PlayerResourceCollection;
use App\Http\Resources\User\Player\PlayerResource;*/

use App\Http\Resources\User\Player\PlayerResourceCollection;
use App\Http\Resources\User\Player\PlayerResource;

use App\Models\Player\PlayerDetail;
use App\Models\Player\Player;
use App\Models\Room\RoomUser;
use App\Models\Room\Room;
use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Http\Requests\User\Registration\PlayerRegistrationRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Common\PremiumTournament;
use App\Models\Common\Credit;
use App\Models\Room\Credit\Transaction;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Tournament\Tournament;

class PremiumTournamentController extends Controller
{
	use ResponseTrait;
	/**
	 * @var PlayerService
	 */
	protected PlayerService $player;
	protected RoomService $room;
	protected UserService $user;
	/**
	 * @param PlayerService $player
	 */
	public function __construct(PlayerService $player, RoomService $room, UserService $user)
	{
		$this->player = $player;
		$this->room = $room;
		$this->user = $user;
	}

	/**
	 * @return PlayerResourceCollection
	 */
	public function index(Request $request)
	{
		$room_id = $request->user()->room->id;
		$premium_tournaments = DB::table('premium_tournaments')
			->select('premium_tournaments.*', 'tournaments.title', 'tournament_details.startday as trnstartday')
			->join('tournaments', 'tournaments.id', '=', 'premium_tournaments.tournament_id')
			->join('rooms', 'rooms.id', '=', 'premium_tournaments.room_id')
			->join('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
			->where('premium_tournaments.room_id', $room_id)->get();
		//$premium_tournaments=PremiumTournament::where('room_id',$room_id)->get();
		if ($premium_tournaments) {
			$response = array(
				'status' => true,
				'data' => $premium_tournaments
			);
		} else {
			$response = array(
				'status' => true,
				'data' => []
			);
		}
		return json_encode($response);
	}
	public function getroomtournamets(Request $request)
	{
		$room_id = $request->user()->room->id;
		//$tournamets=Tournament::where('room_id', "=", $room_id)->get();
		$tournamets = DB::table('tournaments')
			->select('tournaments.*', 'tournament_details.startday as startday')
			->join('tournament_details', 'tournaments.id', '=', 'tournament_details.tournament_id')
			->where('tournaments.room_id', "=", $room_id)
			->where('tournament_details.startday', ">=", date('Y-m-d h:i:s'))
      ->orderBy('tournament_details.startday')
			->get();
		if ($tournamets) {
			$response = array(
				'status' => true,
				'data' => $tournamets
			);
		} else {
			$response = array(
				'status' => true,
				'data' => []
			);
		}
		return json_encode($response);
	}
	public function store(Request $request)
	{
		$data = $request->all();
		$startdate_post = $data['startdate'];
		$tournament_id = $data['tournament_id'];
		$pts = PremiumTournament::where('tournament_id', "=", $tournament_id)->get();
		if ($pts) {
			foreach ($pts as $pt) {
				$today = strtotime($startdate_post);
				$exp = strtotime($pt->startdate);
				if ($today == $exp) {
					$response = array(
						'status' => false,
						'message' => 'PremiumTournament already added between week!!!'
					);
					return json_encode($response);
				}
			}
		}
		$enddate_post = date('Y-m-d 23:59:59', strtotime($data['startdate'] . ' + 6 days'));
		$room_id = $request->user()->room->id;
		$credits = Credit::All();

		$key = "premium_tournament";
		foreach ($credits as $credit) {
			if ($credit->key == $key) {
				$credit_amount = $credit->perday;
			}
		}
		//$transaction = Transaction::where('room_id', "=", $room_id)->orderBy('id','DESC')->first();

		$room = Room::find($room_id);
		$transaction_amount = $room->credits;

		//$transaction_amount=Transaction::where('room_id', "=", $room_id)->sum('amount');

		if ($transaction_amount) {
			if ($credit_amount > $transaction_amount) {
				$response = array(
					'status' => true,
					'message' => 'Credit Insufficient'
				);
				return json_encode($response);
			}
		} else {
			$response = array(
				'status' => true,
				'message' => 'Credit Insufficient'
			);
			return json_encode($response);
		}

		$premium_tournament =  new PremiumTournament;
		$premium_tournament->room_id = $room_id;
		$premium_tournament->startdate = $startdate_post;
		$premium_tournament->enddate = $enddate_post;
		$premium_tournament->tournament_id = $tournament_id;
		if ($premium_tournament->save()) {
			$tr =  new Transaction;
			$tr->amount = -$credit_amount;
			$tr->room_id = $room_id;
			$tr->description = "Purchase Premium Tournament";
			$tr->save();
			$roomUpdateCredit = Room::find($room_id);
			$roomUpdateCredit->credits = $roomUpdateCredit->credits - $credit_amount;
			$roomUpdateCredit->save();
			//$transaction->amount=$transaction_amount-$credit_amount;
			//$transaction->save();
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Added Successfully!!!'
			);

      $mailData = [
        'date' => (new Carbon($startdate_post))->format('d.m.Y')
      ];
			sendEmail(
				'manager',
				'buy_premium_tournament',
				$room_id,
        $mailData
			);

      $mailData['room_title'] = $room->title;
      sendEmail(
        'admin',
        'rm_buy_premium',
        $room_id,
        $mailData,
      );
		} else {
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Not Added!!!'
			);
		}
		return json_encode($response);
	}

	public function destroy($id)
	{
		$credits = Credit::All();
		$premium_tournament = PremiumTournament::where('id', "=", $id)->first();
		foreach ($credits as $credit) {
			if ($credit->key == "premium_tournament") $credit_amount = $credit->perday;
		}
		$startDate = Carbon::parse($premium_tournament->startdate);
		if (Carbon::now() < $startDate) {
			$tr =  new Transaction;
			$tr->amount = $credit_amount;
			$tr->room_id = $premium_tournament->room_id;
			$tr->description = "Premium Tournament Refund";
			$tr->save();
			$roomUpdateCredit = Room::find($premium_tournament->room_id);
			$roomUpdateCredit->credits = $roomUpdateCredit->credits + $credit_amount;
			$roomUpdateCredit->save();
		}

		$premium_tournaments = PremiumTournament::where('id', "=", $id)->delete();
		if ($premium_tournaments) {
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Deleted Successfully!!!'
			);
		} else {
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Not Deleted!!!'
			);
		}
		return json_encode($response);
	}
	public function getTodayPremiumTournament()
	{
		$premium_tournaments = PremiumTournament::get();
		if ($premium_tournaments) {
			$response_array = array();
			foreach ($premium_tournaments as $datas) {
				$startDate = Carbon::parse($datas->startdate);
				$endDate = Carbon::parse($datas->enddate);
				$today = date('Y-m-d');
				$dateTostartCheck = Carbon::parse($today);
				if ($dateTostartCheck->between($startDate, $endDate)) {
					$response_array[] = $datas;
				}
			}
		}
		$response = array(
			'status' => true,
			'data' => $response_array
		);
		return json_encode($response);
	}
	public function edit($id)
	{
		$premium_tournaments = PremiumTournament::find($id);
		if ($premium_tournaments) {
			$response = array(
				'status' => true,
				'data' => $premium_tournaments
			);
		} else {
			$response = array(
				'status' => true,
				'data' => 'No Premium Tournament Found!!!'
			);
		}
		return json_encode($response);
	}
	public function update(Request $request, $id)
	{

		$data = $request->all();
		$room_id = $request->user()->room->id;
		$startdate_post = $data['startdate'];
		//$enddate_post=$data['enddate'];
		$enddate_post = date('Y-m-d h:i:s', strtotime($data['startdate'] . ' + 6 days'));
		$tournament_id = $data['tournament_id'];
		$premium_tournament = PremiumTournament::find($id);
		$premium_tournament->room_id = $room_id;
		$premium_tournament->startdate = $startdate_post;
		$premium_tournament->enddate = $enddate_post;
		$premium_tournament->tournament_id = $tournament_id;

		if ($premium_tournament->save()) {
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Updated Successfully!!!'
			);
		} else {
			$response = array(
				'status' => true,
				'message' => 'Premium Tournament Not Updated!!!'
			);
		}
		return json_encode($response);
	}
	public function getCredit(Request $request)
	{
		$credits = Credit::all();
		if ($credits) {
			$res = array(
				'status' => true,
				'data' => $credits
			);
			return json_encode($res);
		}
		$res = array(
			'status' => false,
			'data' => ''
		);
		return json_encode($res);
	}
	public function getPremiumweekly(Request $request)
	{
		$year   = date("Y");
		$ptarray = array();
		$ptarrayall = array();
		if (date('D') != 'Mon') {
			$staticstart = date('Y-m-d', strtotime('last Monday'));
		} else {
			$staticstart = date('Y-m-d');
		}
		$room_id = $request->user()->room->id;
		$roomdetails = Room::find($room_id);
		$dateWeek = $this->getWeek($staticstart, $year . '-12-31', 0);
		$dateMon = $this->getMonday($staticstart, $year . '-12-31', 0);
		$dateSun = $this->getSunday($staticstart, $year . '-12-31', 0);
		$premiumTournaments = PremiumTournament::get();
		//echo count($dateWeek);die;
		for ($i = 0; $i <= min(30, count($dateSun) - 1); $i++) {
			$topcnt = 0;
			$topcntall = 0;
			foreach ($premiumTournaments as $tournament) {
				$startDate = Carbon::parse($tournament->startdate);
				$endDate = Carbon::parse($tournament->enddate);
				$dateTostartCheck = Carbon::parse($dateMon[$i]);
				$dateToendCheck = Carbon::parse($dateSun[$i]);
				if ($dateTostartCheck->between($startDate, $endDate) || $dateToendCheck->between($startDate, $endDate)) {
					if ($tournament->room_id == $room_id) {
						$topcnt++;
					}
					$topcntall++;
				}
			}
			$ptarray[$i] = $topcnt;
			$ptarrayall[$i] = $topcntall;
		}
		$max = Credit::where('key', 'premium_tournament')->first();

		$data = array(
			'status' => true,
			'week' => $dateWeek,
			'startdates' => $dateMon,
			'enddates' => $dateSun,
			'pt_select' => $ptarray,
			'pt_select_all' => $ptarrayall,
			'max_number_premium' => $max->discount,
			'roomdetails' => $roomdetails
		);
		return json_encode($data);
	}
	function getWeek($startDate, $endDate, $weekNum)
	{
		$endDate = strtotime($endDate);
		$dateWeek = array();
		$cnt = 0;
		for ($i = strtotime('Monday', strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i)) {
			if ($cnt <= 30) {
				$dateWeek[] = date('W', $i);
			}
			$cnt++;
		}
		return ($dateWeek);
	}
	function getMonday($startDate, $endDate, $weekNum)
	{
		$endDate = strtotime($endDate);
		$dateMon = array();
		$cnt = 0;
		for ($i = strtotime('Monday', strtotime($startDate)); $i <= $endDate; $i = strtotime('+1 week', $i)) {
			if ($cnt <= 30) {
				$dateMon[] = date('Y-m-d', $i);
			}
			$cnt++;
		}
		return ($dateMon);
	}
	function getSunday($startDt, $endDt, $weekNum)
	{
		$endDate = strtotime($endDt);
		$dateSun = array();
		$cnt = 0;
		for ($i = strtotime('Sunday', strtotime($startDt)); $i <= $endDate; $i = strtotime('+1 week', $i)) {
			if ($cnt <= 30) {
				$dateSun[] = date('Y-m-d', $i);
			}
			$cnt++;
		}
		return ($dateSun);
	}
}
