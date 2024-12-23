<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User\UserResourceCollection;
use App\Traits\Response\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Models\User\User;

class UserController extends Controller
{
  use ResponseTrait;

  /**
   * @var UserService
   * @var RoomService
   */
  protected UserService $user;

  /**
   * @param UserService $user
   */
  public function __construct(UserService $user)
  {
    $this->user = $user;
  }

  public function index(Request $request)
  {

    return UserResourceCollection::make(
      $this->user->getUserByRole($request->type, '')
    );
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function players()
  {
    return UserResourceCollection::make(
      $this->user->getUserByRole('Player', '')
    );
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function admins()
  {
    return UserResourceCollection::make(
      $this->user->getUserByRole('Admin', '')
    );
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function managers()
  {
    return UserResourceCollection::make(
      $this->user->getUserByRole('Room Manager', '')
    );
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function directors()
  {
    return UserResourceCollection::make(
      $this->user->getUserByRole('Director', '')
    );
  }


  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function loginuser(Request $request)
  {
    /*return UserResourceCollection::make(
        $this->user->getUserByRole('Director','')
    );*/
    $userLogin = $this->user->adminLoginUser(
      $request->id, $request->device_name
    );

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
   * Display the specified resource.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    $users = DB::table('users')
      ->select('user_profiles.*', 'users.email')
      ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
      ->where('users.id', $id)
      ->get();
    $role = User::find($id)->roles->pluck('name')[0];

    if ($users) {
      $res = array(
        'status' => true,
        'data' => $users,
        'role' => $role,
      );
      return json_encode($res);
    }
    $res = array(
      'status' => false,
      'data' => []
    );
    return json_encode($res);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function update(Request $request, int $id): JsonResponse
  {
    $data = $request->all();
    $user = User::find($id);

    if (!empty($data['newpassword'])) {
      $user->password = Hash::make($data['newpassword']);
      $user->save();
    }

    if (!empty($data['email'])) {
      $user->email = $data['email'];
      $user->save();
    }
    unset($data['newpassword']);
    unset($data['email']);

    //$request->user()->profile()->update($data);
    $user->profile()->update($data);

    return $this->jsonResponseSuccess(
      trans('user/profile/profile.update.success')
    );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $user = User::find($id)->delete();
    return $this->jsonResponseSuccess(
      trans('user/user/user.delete.success')
    );

  }

  /**
   * @param array $data
   * @return array
   */
  protected function hashPassword(array $data): array
  {
    $data['password'] = Hash::make($data['password']);

    return $data;
  }

  public function updatestatus(Request $request, $id)
  {
    //update status active in active
    $this->user->update(
      $id,
      ['status' => $request->status]
    );
    return $this->jsonResponseSuccess(
      trans('admin/user/update.success')
    );
  }

  public function verified($id)
  {
    //update status active in active
    $this->user->update(
      $id,
      ['email_verified_at' => date('Y-m-d h:i:s')]
    );
    return $this->jsonResponseSuccess(
      trans('admin/user/verified.success')
    );
  }
}
