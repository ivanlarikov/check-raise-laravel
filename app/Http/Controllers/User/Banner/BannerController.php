<?php

namespace App\Http\Controllers\User\Banner;

use App\Http\Controllers\Controller;
use App\Services\User\Player\PlayerService;
use App\Services\Room\RoomService;

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
use App\Models\Common\Banner;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Common\Credit;
use App\Models\Room\Credit\Transaction;
use App\Models\Common\Setting;

class BannerController extends Controller
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
		$banners = Banner::where('room_id', $room_id)->get();

		if ($banners) {
			$response = array(
				'status' => true,
				'data' => $banners
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
		$enddate_post = date('Y-m-d 23:59:59', strtotime($data['startdate'] . ' + 6 days'));
		$room_id = $request->user()->room->id;
		$banner_datas = Banner::get();
		$credits = Credit::All();
		//$transaction = Transaction::where('room_id', "=", $room_id)->orderBy('id','DESC')->first();

		/*if(!$transaction){
			$response=array(
				'status'=>true,
				'message'=>'Credit Insufficient'
			);
			return json_encode($response);
		}else{
			$transaction_amount=Transaction::where('room_id', "=", $room_id)->sum('amount');
			//$transaction_amount=$transaction->amount;
		}*/
		$room = Room::find($room_id);
		$transaction_amount = $room->credits;
		if ($data['location'] == 1) {
			$key = "top_banner";
		} else {
			$key = "bottom_banner";
		}
		foreach ($credits as $credit) {
			if ($credit->key == $key) {
				$credit_amount = $credit->perday;
				$max_banner = $credit->discount;
			}
		}
		if ($credit_amount > $transaction_amount) {
			$response = array(
				'status' => true,
				'message' => 'Credit Insufficient'
			);
			return json_encode($response);
		}
		if ($banner_datas) {
			$cnt = 0;
			foreach ($banner_datas as $datas) {
				$startDate = Carbon::parse($datas->startdate);
				$endDate = Carbon::parse($datas->enddate);
				$dateTostartCheck = Carbon::parse($startdate_post);
				$dateToendCheck = Carbon::parse($enddate_post);
				if ($dateTostartCheck->between($startDate, $endDate) || $dateToendCheck->between($startDate, $endDate)) {
					if ($datas->location == $data['location']) {
						$cnt++;
						if ($cnt >= $max_banner) {
							$response = array(
								'status' => true,
								'message' => 'Banner already added between dates!!!'
							);
							return json_encode($response);
						}
					}
				}
			}
		}

		if (!empty($data['image'])) {
			if (strlen($data['image']) > 30) {
				$data['image'] = $this->uploadImage($data['image']);
			}
		}
		$banner =  new Banner;
		$banner->room_id = $room_id;
		$banner->startdate = $data['startdate'];
		$banner->enddate = $enddate_post;
		$banner->image = $data['image'];
		$banner->location = $data['location'];
		$banner->url = $data['url'];
		if ($banner->save()) {
			$tr =  new Transaction;
			$tr->amount = -$credit_amount;
			$tr->room_id = $room_id;
			$tr->description = "Purchase Banner";
			$tr->save();
			$roomUpdateCredit = Room::find($room_id);
			$roomUpdateCredit->credits = $roomUpdateCredit->credits - $credit_amount;
			$roomUpdateCredit->save();
			$response = array(
				'status' => true,
				'message' => 'Banner Added Successfully!!!'
			);

      $mailData = [
        'banner_type' => $data['location'] == 1 ? 'Top Banner' : 'Central Banner',
        'date' => (new Carbon($data['startdate']))->format('d.m.Y')
      ];
			sendEmail(
				'manager',
				'buy_banner',
				$room_id,
				$mailData
			);

      $mailData['room_title'] = $room->title;
      sendEmail(
        'admin',
        'rm_buy_banner',
        $room_id,
        $mailData
      );
		} else {
			$response = array(
				'status' => true,
				'message' => 'Banner Not Added!!!'
			);
		}
		return json_encode($response);
	}
	private function uploadImage($base64data)
	{
		$name = uniqid() . '.png';
		$file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
		Storage::disk('banner')->put($name, $file);
		return $name;
	}
	public function destroy($id)
	{
		$credits = Credit::All();
		$banner = Banner::where('id', "=", $id)->first();
		foreach ($credits as $credit) {
			if ($banner->location == 1) {
				if ($credit->key == "top_banner") $credit_amount = $credit->perday;
			} else {
				if ($credit->key == "bottom_banner") $credit_amount = $credit->perday;
			}
		}
		$banner = Banner::where('id', "=", $id)->first();
		$startDate = Carbon::parse($banner->startdate);
		if (Carbon::now() < $startDate) {
			$tr =  new Transaction;
			$tr->amount = $credit_amount;
			$tr->room_id = $banner->room_id;
			$tr->description = "Banner Refund";
			$tr->save();
			$roomUpdateCredit = Room::find($banner->room_id);
			$roomUpdateCredit->credits = $roomUpdateCredit->credits + $credit_amount;
			$roomUpdateCredit->save();
		}
		$banner = Banner::where('id', "=", $id)->delete();
		if ($banner) {
			$response = array(
				'status' => true,
				'message' => 'Banner Deleted Successfully!!!'
			);
		} else {
			$response = array(
				'status' => true,
				'message' => 'Banner Not Deleted!!!'
			);
		}
		return json_encode($response);
	}
	public function getTodayBanner($location)
	{
		$banners = Banner::inRandomOrder()->get();
		$settings = Setting::all();
		if ($banners) {
			$response_array = array();

			foreach ($banners as $datas) {
				$startDate = Carbon::parse($datas->startdate);
				$endDate = Carbon::parse($datas->enddate);
				$today = date('Y-m-d');
				$dateTostartCheck = Carbon::parse($today);
				if ($dateTostartCheck->between($startDate, $endDate)) {
					if ($datas->location == $location) {
						$response_array[] = $datas;
					}
				}
			}
			if ($location == 1) {
				if ($settings[0]->is_display_default_banner_top == 1) {
					$credits = Credit::where('key', 'top_banner')->first();
					if (count($response_array) < $credits->discount) {
						$data = array(
							'image' => $settings[0]->default_banner_top,
							'url' => $settings[0]->default_banner_top_link,
							'rolling_time_top' => $settings[0]->rolling_time_top,
						);
						$response_array[] = $data;
					}
				}
			}
			if ($location == 2) {
				if ($settings[0]->is_display_default_banner_bottom == 1) {
					$credits = Credit::where('key', 'bottom_banner')->first();
					if (count($response_array) < $credits->discount) {
						$data = array(
							'image' => $settings[0]->default_banner_bottom,
							'url' => $settings[0]->default_banner_bottom_link,
							'rolling_time_top' => $settings[0]->rolling_time_bottom,
						);
						$response_array[] = $data;
					}
				}
			}
		}
		$response = array(
			'status' => true,
			'data' => $response_array,
			'settings' => $settings,
		);
		return json_encode($response);
	}
	public function edit($id)
	{
		$banner = Banner::find($id);
		if ($banner) {
			$response = array(
				'status' => true,
				'data' => $banner
			);
		} else {
			$response = array(
				'status' => true,
				'data' => 'No Banner Found!!!'
			);
		}
		return json_encode($response);
	}
	public function update(Request $request, $id)
	{

		$data = $request->all();
		$startdate_post = $data['startdate'];
		$enddate_post = date('Y-m-d 23:59:59', strtotime($data['startdate'] . ' + 6 days'));
		$banner_datas = Banner::get();
		if ($banner_datas) {
			$cnt = 0;
			foreach ($banner_datas as $datas) {
				$startDate = Carbon::parse($datas->startdate);
				$endDate = Carbon::parse($datas->enddate);
				$dateTostartCheck = Carbon::parse($startdate_post);
				$dateToendCheck = Carbon::parse($enddate_post);
				if ($dateTostartCheck->between($startDate, $endDate) || $dateToendCheck->between($startDate, $endDate)) {
					if ($datas->location == $data['location']) {
						$cnt++;
						if ($cnt >= 3) {
							$response = array(
								'status' => true,
								'message' => 'Banner already added between dates!!!'
							);
							return json_encode($response);
						}
					}
				}
			}
		}

		$room_id = $request->user()->room->id;
		$banner = Banner::find($id);
		if (!empty($data['image'])) {
			if (strlen($data['image']) > 30) {
				$data['image'] = $this->uploadImage($data['image']);
			}
			$banner->image = $data['image'];
		}
		$banner->room_id = $room_id;
		$banner->startdate = $startdate_post;
		$banner->enddate = $enddate_post;
		$banner->location = $data['location'];
		$banner->url = $data['url'];
		if ($banner->save()) {
			$response = array(
				'status' => true,
				'message' => 'Banner Updated Successfully!!!'
			);
		} else {
			$response = array(
				'status' => true,
				'message' => 'Banner Not Updated!!!'
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
	public function getbannerweekly(Request $request)
	{
		$year   = date("Y");
		$toprent = array();
		$bottomrent = array();
		$toprentall = array();
		$bottomrentall = array();
		if (date('D') != 'Mon') {
			$staticstart = date('Y-m-d', strtotime('last Monday'));
		} else {
			$staticstart = date('Y-m-d');
		}
		$dateWeek = $this->getWeek($staticstart, $year . '-12-31', 0);
		$dateMon = $this->getMonday($staticstart, $year . '-12-31', 0);
		$dateSun = $this->getSunday($staticstart, $year . '-12-31', 0);
		$banner_datas = Banner::get();
		$room_id = $request->user()->room->id;
		$roomdetails = Room::find($room_id);
		//echo count($dateWeek);die;
		for ($i = 0; $i <= min(30, count($dateSun) - 1); $i++) {
			$topcnt = 0;
			$bottomcnt = 0;
			$topcntall = 0;
			$bottomcntall = 0;
			foreach ($banner_datas as $datas) {
				$startDate = Carbon::parse($datas->startdate);
				$endDate = Carbon::parse($datas->enddate);
				$dateTostartCheck = Carbon::parse($dateMon[$i]);
				$dateToendCheck = Carbon::parse($dateSun[$i]);
				if ($dateTostartCheck->between($startDate, $endDate) || $dateToendCheck->between($startDate, $endDate)) {
					if ($datas->location == 1) {
						if ($datas->room_id == $room_id) {
							$topcnt++;
						}
						$topcntall++;
					}
					if ($datas->location == 2) {
						if ($datas->room_id == $room_id) {
							$bottomcnt++;
						}
						$bottomcntall++;
					}
				}
			}
			$toprent[$i] = $topcnt;
			$bottomrent[$i] = $bottomcnt;
			$toprentall[$i] = $topcntall;
			$bottomrentall[$i] = $bottomcntall;
		}
		$max_number_top = Credit::where('key', 'top_banner')->first();
		$max_number_bottom = Credit::where('key', 'bottom_banner')->first();
		$data = array(
			'status' => true,
			'week' => $dateWeek,
			'startdates' => $dateMon,
			'enddates' => $dateSun,
			'toprent' => $toprent,
			'bottomrent' => $bottomrent,
			'toprentall' => $toprentall,
			'bottomrentall' => $bottomrentall,
			'max_number_top' => $max_number_top->discount,
			'max_number_bottom' => $max_number_bottom->discount,
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
