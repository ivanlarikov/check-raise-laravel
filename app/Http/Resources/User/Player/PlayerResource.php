<?php
namespace App\Http\Resources\User\Player;
use App\Http\Resources\Tournament\User\TournamentUserResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
       
        return [
            'id' => $this->id,
            'users'=>TournamentUserResourceCollection::make(
                // need to paginate
                $this->room_users->sortByDesc('id')->take(1000),$this->id
            ),
        ];
    }
}
