<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\ZipcodeRequest;
use App\Traits\Response\ResponseTrait;
use App\Services\Common\ZipcodeService;
use App\Services\Common\PopupService;
use App\Services\Common\CreditService;
use App\Http\Resources\Common\ZipcodeResource;
use App\Http\Resources\Common\PopupResource;
use App\Http\Requests\Common\PopupSearchRequest;
use App\Http\Resources\Common\CreditResourceCollection;
use App\Models\Common\PageSetting;
use App\Models\Common\Setting;
use App\Models\Room\Room;

use Illuminate\Http\Request;

class CommonController extends Controller
{
    use ResponseTrait;

    /**
     * @var ContactService
     */
    protected ZipcodeService $zipcode;
    protected PopupService $popup;
    protected CreditService $credit;

    /**
     * @param ZipcodeService $zipcode
     */

    public function __construct(ZipcodeService $zipcode, PopupService $popup, CreditService $credit)
    {
        $this->zipcode = $zipcode;
        $this->popup = $popup;
        $this->credit = $credit;
    }

    /**
     * @param ZipcodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function zipcode(ZipcodeRequest $request)
    {
        $data = $request->validated();

        $zipcode = $this->zipcode->show(
            ['code' => $data['code']]
        );
        if ($zipcode) {
            return new ZipcodeResource(
                $zipcode
            );
        }

        return $this->jsonResponseFail(
            trans('common/contact.create.fail'),
            400
        );
    }

    public function popup(PopupSearchRequest $request)
    {
        $data = $request->validated();

        $popup = $this->popup->show(
            [
                'popup_key' => $data['popup_key'],
                'language' => $data['language']
            ]
        );
        if ($popup) {
            return new PopupResource(
                $popup
            );
        }

        return $this->jsonResponseFail(
            trans('common/contact.create.fail'),
            400
        );
    }
    public function credits()
    {
        return CreditResourceCollection::make(
            $this->credit->all(null, null, null, null, null, null, null, false)
        );
    }

    // Return site setting.
    public function settings(Request $request)
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

    public function pagesetting(Request $request)
    {
        $settings = PageSetting::all();
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
    public function getrooms(Request $request)
    {
        $status = $request->input('status');
        $query = Room::select('id', 'title');

        if ($status && $status !== 'undefined' && $status !== "null") {
            $query->where('status', $status);
        }

        $rooms = $query->orderBy('title')->get();

        if ($rooms) {
            $res = array(
                'status' => true,
                'data' => $rooms
            );
            return json_encode($res);
        }
        $res = array(
            'status' => false,
            'data' => []
        );
        return json_encode($res);
    }
}
