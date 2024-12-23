<?php

namespace App\Http\Controllers\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Traits\Response\ResponseTrait;
use App\Models\Common\Banner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
  use ResponseTrait;

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function index(Request $request): JsonResponse
  {
    $banners = Banner::with('room')->get();
    return $this->jsonResponseSuccess(['banners' => $banners]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function show(Request $request, int $id): JsonResponse
  {
    $banner = Banner::find($id);
    return $this->jsonResponseSuccess(['data' => $banner]);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function update(Request $request, $id): JsonResponse
  {
    try {
      $data = $request->all();
      $postStartDate = $data['startdate'];
      $postEndDate = date('Y-m-d 23:59:59', strtotime($data['startdate'] . ' + 6 days'));

      $banner_datas = Banner::get();
      if ($banner_datas) {
        $cnt = 0;
        foreach ($banner_datas as $datas) {
          $startDate = Carbon::parse($datas->startdate);
          $endDate = Carbon::parse($datas->enddate);
          $dateTostartCheck = Carbon::parse($postStartDate);
          $dateToendCheck = Carbon::parse($postEndDate);
          if ($dateTostartCheck->between($startDate, $endDate) || $dateToendCheck->between($startDate, $endDate)) {
            if ($datas->location == $data['location']) {
              $cnt++;
              if ($cnt >= 3) {
                return $this->jsonResponseFail(['message' => 'Banner already added between dates!!!']);
              }
            }
          }
        }
      }

      $banner = Banner::find($id);
      if (!empty($data['image']) && strlen($data['image']) > 30) {
        $data['image'] = $this->uploadImage($data['image']);
        $banner->image = $data['image'];
      }
      $banner->room_id = $data['room_id'];
      $banner->startdate = $postStartDate;
      $banner->enddate = $postEndDate;
      $banner->location = $data['location'];
      $banner->url = $data['url'];
      $banner->save();

      return $this->jsonResponseSuccess([
        'message' => 'Banner Updated Successfully!!!'
      ]);
    } catch (\Throwable $e) {
      return $this->jsonResponseFail([
        'message' => 'Banner Not Updated!!!',
        'error' => $e->getMessage()
      ]);
    }
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function destroy(Request $request, int $id): JsonResponse
  {
    Banner::destroy($id);

    return $this->jsonResponseSuccess();
  }

  /**
   * @param $base64data
   * @return string uploaded file name
   */
  private function uploadImage($base64data): string
  {
    $name = uniqid() . '.png';
    $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
    Storage::disk('banner')->put($name, $file);
    return $name;
  }
}
