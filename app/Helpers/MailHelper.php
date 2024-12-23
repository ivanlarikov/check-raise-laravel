<?php

use App\Models\Common\EmailLog;
use App\Models\Common\SiteMeta;
use App\Models\Notification\Notification;
use App\Models\Room\Room;
use App\Models\User\User;
use Illuminate\Support\Facades\Mail;

function getNotification($type, $slug)
{
  $notification = Notification::where(['type' => $type, 'slug' => $slug])->first();
  return $notification;
}

function getEmailSetting()
{
  $emailSetting = SiteMeta::whereIn('key', ['custom_email', 'email_setting'])->get()->pluck('value', 'key')->all();
  return $emailSetting;
}

function getStatusText($setting)
{
  $status = '';

  switch ($setting['email_setting']) {
    case 'send':
      $status = 'Sent';
      break;
    case 'block':
      $status = 'Blocked';
      break;
    case 'custom':
      $status = 'Sent to ' . $setting['custom_email'];
      break;
    default:
      break;
  }
  return $status;
}

function replaceContent($content, $variables, $contentVariables)
{
  foreach ($variables as $var) {
    $content = str_replace('{{' . $var . '}}', $contentVariables[$var], $content);
  }

  return $content;
}

function getReceiver($type, $id)
{
  $data = ['lang' => 'en', 'email' => ''];

  switch ($type) {
    case 'player':
      $user = User::with('profile')->find($id);
      $data['lang'] = $user->profile->language ?? 'en';
      $data['email'] = $user->email;
      break;
    case 'manager':
      $room = Room::with('detail')->find($id);
      $data['lang'] = !empty($room->detail->activelanguages[0]) ? $room->detail->activelanguages[0] : 'en';
      $data['email'] = $room->detail->contact;
      break;
    case 'admin':
      $data['lang'] = 'en';
      $data['email'] = env('MAIL_ADMIN_ADDRESS') ?? 'checkraise.ch@gmail.com';
      break;
    default:
      break;
  }

  return $data;
}

function sendEmail($notificationType, $notificationSlug, $userId, $contentVariables, $customEmail = null)
{
  $emailSetting = getEmailSetting();
  $receiver = getReceiver($notificationType, $userId);

  $from = env('MAIL_NOREPLY_ADDRESS') ?? 'info@checkraise.ch';
  $to = $customEmail ?? $receiver['email'];

  $notification = getNotification($notificationType, $notificationSlug);
  $subject = $notification->title[$receiver['lang']];
  $content = replaceContent(
    $notification->content[$receiver['lang']],
    $notification->variables,
    $contentVariables
  );

  EmailLog::create([
    'to' => $to,
    'from' => $from,
    'subject' => $subject,
    'content' => $content,
    'status' => !$notification->status ? 'Blocked' : getStatusText($emailSetting),
  ]);

  if ($notification->status && $emailSetting['email_setting'] !==  'block') {
    $to = $emailSetting['email_setting'] === 'send' ? $to : $emailSetting['custom_email'];

    Mail::send([], [], function ($m) use ($to, $subject, $content) {
      $m->to($to)->subject($subject)->html($content);
    });
  }
}
