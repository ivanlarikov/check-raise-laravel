<?php

namespace App\Http\Controllers\User\Session;

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\LoginRequest;
use App\Models\User\User;
use App\Models\User\UserVerification;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class SessionController extends UserController
{
  use ResponseTrait;

  /**
   * @param LoginRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
  {
    $userLogin = $this->user->login(
      $request->validated()
    );

    if ($userLogin === 'unverified') {
      return $this->jsonResponseFail($userLogin, 401);
    }

    if ($userLogin === false) {
      return $this->jsonResponseFail(
        trans('user/session/login.fail'),
        401
      );
    }

    return $this->jsonResponseSuccess(
      $userLogin
    );
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout(Request $request): \Illuminate\Http\JsonResponse
  {
    if (
      $request->user()->currentAccessToken()->delete()
    ) {
      return $this->jsonResponseSuccess(
        trans('user/session/logout.success')
      );
    }

    return $this->jsonResponseSuccess(
      trans('user/session/logout.fail')
    );
  }

  public function verifyEmail(Request $request)
  {
    $request->validate(['token' => 'required']);
    $userVerification = UserVerification::where('token', $request->token)->first();

    if (!$userVerification) {
      return $this->jsonResponseFail([
        'message' => 'Invalid token!'
      ]);
    }

    $data = [
      'email_verified_at' => Carbon::now(),
      'status' => 1,
    ];

    if (!empty($userVerification->email)) {
      $data['email'] = $userVerification->email;
    }

    User::where('id', $userVerification->user_id)->update($data);

    $userVerification->delete();

    return $this->jsonResponseSuccess([
      'message' => 'Successfully verified!'
    ]);
  }

  public function resendVerificationEmail(Request $request)
  {
    $data = $this->hashPassword($request->all());
    $user = User::where([
      'email' => $data['email'],
      'status' => 0
    ])
      ->whereNull('email_verified_at')
      ->first();

    if (empty($user)) {
      return $this->jsonResponseFail('Invalid user!', 401);
    }

    UserVerification::where('user_id', '=', $user->id)->delete();

    $token = Str::random(64);
    UserVerification::updateOrCreate(
      ['user_id' => $user->id,],
      ['token' => $token,]
    );

    $contentVariables = [
      'firstname' => $user->profile->firstname,
      'lastname' => $user->profile->lastname,
      'token' => $token,
    ];

    sendEmail('player', 'register_email_confirm', $user->id, $contentVariables);

    return $this->jsonResponseSuccess('Email sent successfully');
  }

  public function forgotPassword(Request $request)
  {
    $request->validate([
      'email' => 'required|email|exists:users',
    ]);

    $email = $request->email;

    $token = Str::random(64);

    $reset = DB::table('password_reset_tokens')->where('email', $email)->first();

    if (empty($reset)) {
      DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => $token,
        'created_at' => Carbon::now()
      ]);
    } else {
      $token = $reset->token;
    }

    $user = User::with('profile')->where(['email' => $email])->first();

    sendEmail(
      'player',
      'reset_password',
      $user->id,
      [
        'firstname' => $user->profile->firstname,
        'lastname' => $user->profile->lastname,
        'token' => $token
      ]
    );

    return $this->jsonResponseSuccess([
      'message' => 'Sent reset link. Please check your inbox',
    ]);
  }

  public function resetPassword(Request $request)
  {
    $request->validate([
      'email' => 'required|email|exists:users',
      'password' => 'required|string|confirmed',
      'password_confirmation' => 'required',
      'token' => 'required'
    ]);

    $email = $request->email;

    $updatePassword = DB::table('password_reset_tokens')
      ->where([
        'email' => $email,
        'token' => $request->token
      ])
      ->first();

    if (!$updatePassword) {
      return $this->jsonResponseFail([
        'message' => 'Invalid token!'
      ]);
    }

    $user = User::where('email', $email)
      ->update(['password' => Hash::make($request->password)]);

    DB::table('password_reset_tokens')->where(['email' => $email])->delete();

    return $this->jsonResponseSuccess([
      'message' => 'Succeed. Please login with your new password'
    ]);
  }

}
