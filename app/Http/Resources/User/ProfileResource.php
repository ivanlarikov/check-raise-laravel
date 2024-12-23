<?php
namespace App\Http\Resources\User;
use App\Models\Room\Room;
use App\Models\Room\RoomMember;
use App\Models\Room\RoomSetting;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $rooms=Room::get();
		$sr=array();
        $i=0;
        foreach($rooms as $room){
            $sr[$i]['name']= $room->title;
			$room_settings=RoomSetting::where('room_id', "=", $room->id)->first();
			if($room_settings){
				if($room_settings->is_membership==0){
					$sr[$i]['memberlabel']= "No membership required";
					$sr[$i]['memberuntill']= "-";
				}else{
					$id=$request->user()->id;
					$ismember=RoomMember::where('user_id', "=", $id)->where('room_id', "=", $room->id)->first();
					if(!empty($ismember->room_id)){
						$sr[$i]['memberlabel']= "Member";
						$sr[$i]['memberuntill']= $ismember->expiry;
					}else{
						$sr[$i]['memberlabel']= "Not member";
						$sr[$i]['memberuntill']= "-";
					}
				}
			}else{
				$sr[$i]['memberlabel']= "No membership required";
				$sr[$i]['memberuntill']= "-";
			}
            $i++;
        }
   
        //return $sr;
        return [
            'firstname' =>  $this->firstname,
            'lastname'  =>  $this->lastname,
            'email'     =>  $this->user->email,
            'dob'       =>  $this->dob,           
            'street'    =>  $this->street,
            'language'  =>  $this->language,
            'nickname'  =>  $this->nickname,
            'city'      =>  $this->city,
            'zipcode'   =>  $this->zipcode,
            'displayoption' =>  $this->displayoption,
            'phonecode'     =>  $this->phonecode,
            'phonecountry'  =>  $this->phonecountry,
            'phonenumber'   =>  $this->phonenumber,
            'enterprise'   =>  $this->enterprise,
            'room'=>$sr
        ];
    }
}
