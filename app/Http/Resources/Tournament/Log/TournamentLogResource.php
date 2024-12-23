<?php

namespace App\Http\Resources\Tournament\Log;
use App\Models\Tournament\TournamentDetail;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {   
        if(isset($this->user->profile->firstname)){
            $firstname=$this->user->profile->firstname;
        }else{
            $firstname='-';
        }
        if(isset($this->user->profile->lastname)){
            $lastname=$this->user->profile->lastname;
        }else{
            $lastname='-';
        }
        $tournament_details=TournamentDetail::where('tournament_id',$this->tournament->id)->first();
        if($tournament_details){
            $tournament_date=$tournament_details->startday;
        }else{
            $tournament_date='';
        }
        return [
            'id' => $this->id,
            'manager'=>$firstname.' '.$lastname,
            'tournament' => $this->tournament->title,
            'tournament_date' =>$tournament_date ,
            'changes'=>$this->changes,
            'datetime'=>$this->created_at,
        ];
    }
}
