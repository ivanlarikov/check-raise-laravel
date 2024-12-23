<?php

namespace App\Http\Controllers\Admin\EmailLog;

use App\Http\Controllers\Controller;
use App\Models\Common\EmailLog;
use App\Models\Common\SiteMeta;
use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailLogController extends Controller
{
    use ResponseTrait;
    /**
     * @var PlayerService
     */
    /**
     * @param PlayerService $player
     */
    public function __construct()
    {
    }

    /**
     * @return PlayerResourceCollection
     */
    public function index(Request $request)
    {
        $emailLog = EmailLog::All();

        if ($emailLog) {
            return $this->jsonResponseSuccess([
                'data' => $emailLog
            ]);
        }

        return $this->jsonResponseFail([
            'data' => []
        ]);
    }

    public function show($id)
    {
        $emailLog = EmailLog::find($id);
        return $this->jsonResponseSuccess([
            'data' => $emailLog
        ]);
    }

    public function getEmailSetting()
    {
        $setting = SiteMeta::whereIn('key', ['email_setting', 'custom_email'])->get()->pluck('value', 'key');

        return $this->jsonResponseSuccess([
            'data' => $setting
        ]);
    }

    public function updateEmailSetting(Request $request)
    {
        $emailSetting = $request->email_setting;
        $customEmail = $request->custom_email ?? '';

        SiteMeta::updateOrCreate(['key' => 'email_setting'], ['value' => $emailSetting]);
        SiteMeta::updateOrCreate(['key' => 'custom_email'], ['value' => $customEmail]);

        return $this->jsonResponseSuccess([
            'email_setting' => $emailSetting,
            'custom_email' => $customEmail
        ]);
    }
}
