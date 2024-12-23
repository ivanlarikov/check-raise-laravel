<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use App\Services\Room\RoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @var UserService
     * @var RoomService
     */
    protected UserService $user;
    protected RoomService $room;
    /**
     * @param UserService $user
     * @param RoomService $room
     */
    public function __construct(UserService $user,RoomService $room)
    {
        $this->user = $user;
        $this->room = $room;
        /*$this->authorizeResource($this->user->eloquentModel(), 'user', [
            'except' => [ 'store' ]
        ]);*/
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
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
}
