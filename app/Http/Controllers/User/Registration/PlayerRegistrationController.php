<?php

namespace App\Http\Controllers\User\Registration;

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\Registration\PlayerRegistrationRequest;
use App\Models\User\UserVerification;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;


class PlayerRegistrationController extends UserController
{
  use ResponseTrait;

  /**
   * @param PlayerRegistrationRequest $request
   * @return JsonResponse
   */
  public function store(PlayerRegistrationRequest $request): JsonResponse
  {
    $data = $this->hashPassword($request->validated());
    $user = $this->user->create($data);
    if ($user) {
      $user->assignRole('Player');
      //add user profile
      $user->profile()->create($data);

      $token = Str::random(64);
      UserVerification::updateOrCreate(
        ['user_id' => $user->id,],
        ['token' => $token,]
      );

      $contentVariables = [
        'firstname' => $data['firstname'],
        'lastname' => $data['lastname'],
        'token' => $token,
      ];

      sendEmail('player', 'register_email_confirm', $user->id, $contentVariables);

      $languages = ['en' => 'English', 'fr' => 'French', 'de' => 'German'];
      $displayOptions = ['public_nic' => 'Name Surname', 'private' => "Nickname (If applicable)", 'anonymous' => 'Anonymous'];

      $data['address'] = $data['street'] . "," . $data['zipcode'] . ' ' . $data['city'];
      $data['dob'] = (new Carbon($data['dob']))->format('d.m.Y');
      $data['phone'] = $data['phonecode'] . ' ' . $data['phonenumber'];
      $data['displayoption'] = $displayOptions[$data['displayoption']];
      $data['language'] = $languages[$data['language']];
      sendEmail(
        'admin',
        'new_player',
        $user->id,
        $data
      );

      return $this->jsonResponseSuccess(
        trans('user/registration/player.create.success')
      );
    }

    return $this->jsonResponseFail(
      trans('user/registration/player.create.fail'),
      400
    );
  }
}
