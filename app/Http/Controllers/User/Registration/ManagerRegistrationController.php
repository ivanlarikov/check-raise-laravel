<?php

namespace App\Http\Controllers\User\Registration;

use App\Http\Controllers\User\UserController;
use App\Http\Requests\User\Registration\ManagerRegistrationRequest;
use App\Traits\Response\ResponseTrait;

class ManagerRegistrationController extends UserController
{
    use ResponseTrait;
    
    /**
     * @param PlayerRegistrationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ManagerRegistrationRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->hashPassword( $request->validated() );
		$data['email_verified_at']=now();
        $data['status']=1;
        $user = $this->user->create(
            $data
        );
        if( $user ){
            $user->assignRole('Room Manager');
            //add user profile 
            $user->profile()->create($data);
            //assigne user 
			
            $data['room']['user_id']=$user->id;
            //create room
            $room=$this->room->create(
                $data['room']
            );
            //save details
            $room->detail()->create($data['room']['details']);
            
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
