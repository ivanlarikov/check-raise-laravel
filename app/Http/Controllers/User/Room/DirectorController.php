<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Services\User\UserService;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Requests\User\Room\RoomCreateRequest;
use App\Http\Requests\User\Room\RoomUpdateRequest;

use App\Http\Requests\User\Room\Director\DirectorUpdateRequest;

use App\Http\Resources\User\Room\RoomResource;
use App\Models\Room\Room;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;

use App\Http\Resources\User\UserResourceCollection;
use App\Http\Resources\User\Room\DirectorResourceCollection;
use Illuminate\Support\Facades\Hash;
use App\Models\User\User;
use App\Models\User\UserProfile;
use App\Models\Room\RoomUser;

class DirectorController extends Controller
{
    use ResponseTrait;
    /**
     * @var RoomService
     */
    protected RoomService $room;
    protected UserService $user;


    /**
     * @param RoomService $tournment
     */
    public function __construct(RoomService $room,UserService $user)

    {
        $this->room = $room;
        $this->user = $user;
    }
    /**
     * @param RoomResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
		
        $allusers=[];
		
        //foreach($request->user()->room as $room){
			if($request->user()->room->room_directors){
				foreach($request->user()->room->room_directors as $user){
					$allusers[]=$user->user_id;  
				}
				$allusers = array_unique($allusers, SORT_REGULAR);
				//list all players of rooms
				if($allusers){
					return DirectorResourceCollection::make(
						$this->user->getUserByIds($allusers)
					);
				}
			}
        //}
        return true;
        //get manual users and room users

    }
	public function store(Request $request){
		$username=$request->name.''.$request->surname;
		$data=array(
			'username'=>str_replace(" ","",$username),
			'email'=>$request->email,
			'password'=>Hash::make($request->password),
            'email_verified_at'=>now(),
            'status'=>1
		);
        //print_r($data);die;
        $user = $this->user->create(
            $data
        );
        if( $user ){

            $user->assignRole('Director');
			//room_directors
			$room = $this->room->show(
				['id' => $request->user()->room->id]
			);
			$room->room_directors()->create(['user_id'=>$user->id]);
			
			$roomuser = new RoomUser();
			//On left field name in DB and on right field name in Form/view/request
			$roomuser->room_id = $request->user()->room->id;
			$roomuser->user_id = $user->id;
			$roomuser->save();
			
			$userprofile = new UserProfile();
			//On left field name in DB and on right field name in Form/view/request
			$userprofile->user_id  = $user->id;
			$userprofile->firstname = $request->name;
			$userprofile->lastname = $request->surname;
			$userprofile->save();
			
            return $this->jsonResponseSuccess(
                trans('user/registration/player.create.success')
            );
        }

        return $this->jsonResponseFail(
            trans('user/registration/player.create.fail'),
            400
        );
	}
    public function update(DirectorUpdateRequest $request){
        $data = $request->validated();
        if(!empty($data['password']))
            $this->user->update($data['user_id'],['password'=>Hash::make($data['password'])]);
        
        //capabilities update 
        $user = User::where(['id' => $data['user_id']])->first();
        //array caps
        $caps=[];
        foreach($data['capabilities'] as $val)
            $caps[]['capability']=$val;
        $user->directory_capabilities()->delete();
        $user->directory_capabilities()->createMany($caps);
        return $this->jsonResponseSuccess(
            trans('user/profile/profile.update.success')
        );
    }
	public function destroy($id)
    {
        User::where('id', "=", $id)->delete();
        return $this->jsonResponseSuccess(
            trans('user/room/room.delete.success')
        );
    }
}