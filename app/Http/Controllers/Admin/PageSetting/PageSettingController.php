<?php

namespace App\Http\Controllers\Admin\PageSetting;

use App\Http\Controllers\Controller;


use App\Traits\Response\ResponseTrait;
use App\Models\Common\PageSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageSettingController extends Controller
{
    use ResponseTrait;

    /**
     * @param SettingResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = PageSetting::query();

        if ($request->key) {
            $query->whereIn('key', explode(',', $request->key));
        }

        $settings = $query->get();
        return $this->jsonResponseSuccess(['data' => $settings]);
    }

    public function show($key)
    {
        $setting = PageSetting::where('key', '=', $key)->first();
        return $this->jsonResponseSuccess(['data' => $setting]);
    }

    public function update(Request $request)
    {
        $key = $request->key;
        $image = $request->image;
        $data = [
            'content' => $request->content
        ];

        if (!empty($image) && strlen($image) > 30) {
            $data['image'] = $this->uploadImage($image);
        }

        PageSetting::updateOrCreate(
            ['key' => $key],
            $data
        );

        return $this->jsonResponseSuccess(['message' => 'Page Setting Updated!']);
    }

    private function uploadImage($base64data)
    {
        $name = uniqid() . '.png';
        $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
        Storage::disk('banner')->put($name, $file);
        return $name;
    }
}
