<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;


use App\Jobs\ProcessScheduledBannerBottom;
use App\Jobs\ProcessScheduledBannerTop;
use App\Traits\Response\ResponseTrait;
use App\Models\Common\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SettingController extends Controller
{
  use ResponseTrait;

  /**
   * @param Request $request
   * @return false|string
   */
  public function index(Request $request)
  {
    $settings = Setting::all();
    if ($settings) {
      $res = array(
        'status' => true,
        'data' => $settings
      );
      return json_encode($res);
    }
    $res = array(
      'status' => false,
      'data' => ''
    );
    return json_encode($res);
  }

  public function update(Request $request, $id)
  {
    $data = Setting::find($id);
    $data->is_important_message_banner = $request->is_important_message_banner;
    $data->en_msg_banner = $request->en_msg_banner;
    $data->fr_msg_banner = $request->fr_msg_banner;
    $data->db_msg_banner = $request->db_msg_banner;
    $data->adv_top_banner = $request->adv_top_banner;
    $data->adv_bottom_banner = $request->adv_bottom_banner;
    $data->is_premium_tournament = $request->is_premium_tournament;
    $data->rolling_time_top = $request->rolling_time_top ? $request->rolling_time_top : 10;
    $data->rolling_time_bottom = $request->rolling_time_bottom ? $request->rolling_time_bottom : 10;
    $data->is_paypal = $request->is_paypal;
    $data->paypal_fee = $request->paypal_fee;
    $data->top_banner_credit = $request->top_banner_credit;
    $data->top_banner_credit_discount = $request->top_banner_credit_discount;
    $data->bottom_banner_credit = $request->bottom_banner_credit;
    $data->bottom_banner_credit_discount = $request->bottom_banner_credit_discount;
    $data->premium_banner_credit = $request->premium_banner_credit;
    $data->premium_banner_credit_discount = $request->premium_banner_credit_discount;
    $data->is_banner_0 = $request->is_banner_0;
    $data->is_banner_1 = $request->is_banner_1;
    $data->is_banner_2 = $request->is_banner_2;
    $data->is_banner_3 = $request->is_banner_3;
    $data->default_banner_top_link = $request->default_banner_top_link;
    $data->default_banner_bottom_link = $request->default_banner_bottom_link;

    $data->next_banner_top_enabled = $request->next_banner_top_enabled;
    $data->next_banner_top_link = $request->next_banner_top_link;
    $data->next_banner_top_start_date = $request->next_banner_top_start_date ?? null;
    $data->next_banner_bottom_enabled = $request->next_banner_bottom_enabled;
    $data->next_banner_bottom_link = $request->next_banner_bottom_link;
    $data->next_banner_bottom_start_date = $request->next_banner_bottom_start_date ?? null;

    if (!empty($request->default_banner_top) && strlen($request->default_banner_top) > 30) {
      $data->default_banner_top = $this->uploadImage($request->default_banner_top);
    }
    if (!empty($request->default_banner_bottom) && strlen($request->default_banner_bottom) > 30) {
      $data->default_banner_bottom = $this->uploadImage($request->default_banner_bottom);
    }

    if (!empty($request->next_banner_top_image) && strlen($request->next_banner_top_image) > 30) {
      $data->next_banner_top_image = $this->uploadImage($request->next_banner_top_image);
    }
    if (!empty($request->next_banner_bottom_image) && strlen($request->next_banner_bottom_image) > 30) {
      $data->next_banner_bottom_image = $this->uploadImage($request->next_banner_bottom_image);
    }
    $updated = $data->save();

    if($request->next_banner_top_enabled) {
      ProcessScheduledBannerTop::dispatch($data)->delay(Carbon::create($request->next_banner_top_start_date));
    }

    if($request->next_banner_bottom_enabled) {
      ProcessScheduledBannerBottom::dispatch($data)->delay(Carbon::create($request->next_banner_bottom_start_date));
    }

    if ($updated) {
      $res = array(
        'status' => true,
        'msg' => "Setting Updated!!!"
      );
      return json_encode($res);
    }

    $res = array(
      'status' => false,
      'msg' => "Setting Not Updated!!!"
    );
    return json_encode($res);
  }

  private function uploadImage($base64data)
  {
    $name = uniqid() . '.png';
    $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
    Storage::disk('banner')->put($name, $file);
    return $name;
  }

  public function defaultBannerUpdate(Request $request, $id, $slug)
  {
    $data = Setting::find($id);
    if ($slug == "top") {
      if ($data->is_display_default_banner_top == 0) {
        $data->is_display_default_banner_top = 1;
      } else {
        $data->is_display_default_banner_top = 0;
      }
    } else {
      if ($data->is_display_default_banner_bottom == 0) {
        $data->is_display_default_banner_bottom = 1;
      } else {
        $data->is_display_default_banner_bottom = 0;
      }
    }
    $data->save();
    $res = array(
      'status' => true,
      'msg' => "Setting Updated!!!"
    );
    return json_encode($res);
  }

  public function bannerPosition(Request $request, $id, $slug)
  {
    $data = Setting::find($id);
    if ($slug == "top") {
      if ($data->adv_top_banner == 0) {
        $data->adv_top_banner = 1;
      } else {
        $data->adv_top_banner = 0;
      }
    } else if ($slug == "bottom") {
      if ($data->adv_bottom_banner == 0) {
        $data->adv_bottom_banner = 1;
      } else {
        $data->adv_bottom_banner = 0;
      }
    } else {
      if ($data->is_premium_tournament == 0) {
        $data->is_premium_tournament = 1;
      } else {
        $data->is_premium_tournament = 0;
      }
    }
    $data->save();
    $res = array(
      'status' => true,
      'msg' => "Setting Updated!!!"
    );
    return json_encode($res);
  }

  public function rollingSetting(Request $request, $id, $slug)
  {
    $data = Setting::find($id);
    if ($slug == "top") {
      $data->rolling_time_top = $request->rolling_time;
    } else {
      $data->rolling_time_bottom = $request->rolling_time;
    }
    $data->save();
    $res = array(
      'status' => true,
      'msg' => "Setting Updated!!!"
    );
    return json_encode($res);
  }

  public function updateBannerInterval(Request $request)
  {
    $setting = Setting::find(1);
    $setting->bottom_desktop_start = $request->bottom_desktop_start;
    $setting->bottom_desktop_interval = $request->bottom_desktop_interval;
    $setting->bottom_mobile_start = $request->bottom_mobile_start;
    $setting->bottom_mobile_interval = $request->bottom_mobile_interval;
    $setting->save();
    $res = array(
      'status' => true,
      'msg' => "Setting Updated!!!"
    );
    return json_encode($res);
  }
}
