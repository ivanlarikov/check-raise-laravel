<?php

namespace App\Services\User;

use App\Http\Requests\User\LoginRequest;
use App\Repositories\User\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use App\Models\User\User;

class UserService extends BaseService
{
    /**
     * @var UserRepository
     */
    protected UserRepository $user;

    /**
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        $this->user = $user;
        parent::__construct($this->user);
    }

    /**
     * @param string $role
     * @return mixed
     */
    public function getUserByRole(string $role,string $keyword)
    {
        return $this->user->getUserByRole($role,$keyword);
    }

    /**
     * @param array $request
     * @return array|false|string
     */
    public function login(array $request): bool|string|array
    {
        $user = $this->user->show(
            [
                'email' => $request['email']
            ],
            [
                'Roles'
            ]
        );

        if (! $user || ! Hash::check($request['password'], $user->password)) {
            return false;
        }
        //check if user is verified or not
        if(!$user->email_verified_at || $user->status==0)
        {
            return 'unverified';
        }
        $userRole = $user->getRoleNames();
        if(empty($userRole[0]))
            return [
                'user'=>$user,
                'type' => $userRole,
                'token' => $user->createToken(
                    $request['device_name']
                )->plainTextToken
            ];

        return [
                'user'=>$user,
                'type' => $userRole,
                'token' => $user->createToken(
                    $request['device_name'],
                    [
                        $userRole[0]
                    ]
                )->plainTextToken,
                //'capabilities'=> $user->directory_capabilities
            ];
    }
    public function adminLoginUser($id,$device_name){
        $user = $this->user->show(
            [
                'id' => $id
            ],
            [
                'Roles'
            ]
        );

        $userRole = $user->getRoleNames();
        if(empty($userRole[0]))
            return [
                'user'=>$user,
                'type' => $userRole,
                'token' => $user->createToken(
                    $device_name
                )->plainTextToken
            ];

        return [
                'user'=>$user,
                'type' => $userRole,
                'token' => $user->createToken(
                    $device_name,
                    [
                        $userRole[0]
                    ]
                )->plainTextToken
            ];
    }
	public function getVendorStore(User $user){
		echo "<pre>";
		print_r($user->vendors()->get());
		die;
	}

    public function getUserByIds($ids){
        return $this->user->getUserByIds($ids);
    }

    /*public function mytournament($UserId){

	}*/

}
