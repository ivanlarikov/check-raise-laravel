<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\Profile\ProfileUpdateRequest;
use App\Models\User\UserVerification;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\User\ProfileResource;
use App\Models\User\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends UserController
{
  use ResponseTrait;

  /**
   * @param ProfileUpdateRequest $request
   * @return JsonResponse
   */
  public function updateProfile(ProfileUpdateRequest $request): JsonResponse
  {
    $data = $request->validated();
    $user = $request->user();
    $newEmail = $data['email'];
    $emailChanged = $newEmail !== $user->email;

    $existUser = User::where('email', $newEmail)->whereNot('id', $user->id)->first();

    if($existUser) {
      return $this->jsonResponseFail(
        trans('This email address is already in use. Please choose another one.')
      );
    }

    if (!empty($data['newpassword'])) {
      $user = User::find($user->id);
      $user->password = Hash::make($data['newpassword']);
      $user->save();
    }
    unset($data['newpassword']);
    unset($data['email']);
    $request->user()->profile()->update($data);

    if ($emailChanged) {
      $token = Str::random(64);
      UserVerification::updateOrCreate(
        ['user_id' => $user->id,],
        ['token' => $token, 'email' => $newEmail]
      );

      sendEmail(
        'player',
        'change_email_confirm',
        $user->id,
        [
          'firstname' => $data['firstname'],
          'lastname' => $data['lastname'],
          'token' => $token,
        ],
        $newEmail
      );
      return $this->jsonResponseSuccess(
        trans('Please verify your new email address.')
      );
    }

    return $this->jsonResponseSuccess(
      trans('user/profile/profile.update.success')
    );
  }

  /**
   * @param Request $request
   * @return ProfileResource
   */
  public function showProfile(Request $request): ProfileResource
  {
    $user = $request->user()->profile;

    return new ProfileResource(
      $user,
    );

    /*$data = $request->validated();

    $request->user()->profile()->update($data);

    return $this->jsonResponseSuccess(
        trans('user/profile/update.success')
    );*/
  }
}
