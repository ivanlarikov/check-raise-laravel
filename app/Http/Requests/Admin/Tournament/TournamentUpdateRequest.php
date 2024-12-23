<?php

namespace App\Http\Requests\Admin\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class TournamentUpdateRequest extends FormRequest
{
    /**
     * Determine if the Admin is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function rules(): array
    {

        return [
            'tournament.id'=>'required',
            'tournament.title'=>'required',
			'tournament.room_id'=>'required',
			'tournament.status'=>'nullable',
            'details.type'  =>  'required',
            'details.isshorthanded'  =>  'nullable',
            'details.dealertype'  =>  'nullable',
            'details.buyin'  =>  'required',
            'details.bounty'  =>  'nullable',
            'details.rake'  =>  'nullable',
            'details.maxreentries'  =>  'required',
            'details.startday'  =>  'required',
            'details.lastday'  =>  'nullable',
            'details.lateregformat'  =>  'nullable',
            'details.lateregtime'  =>  'nullable',
            'details.latereground'  =>  'nullable',
            'details.startingstack'  =>  'required',
            'details.level_duration'  =>  'required',
            'details.maxplayers'  =>  'required',
            'details.reservedplayers'  =>  'nullable',
            'details.ischampionship'  =>  'required',
            'details.bounusdeadline'  =>  'nullable',
            'details.activelanguages'  =>  'nullable',
            
            "descriptions.*.language" => "required",
            "descriptions.*.description" => "nullable",

            "structure.*.order"=>"required",
            "structure.*.sb"=>"nullable",
            "structure.*.bb"=>"nullable",
            "structure.*.ante"=>"nullable",
            "structure.*.duration"=>"nullable",
            "structure.*.isbreak"=>"nullable",
            "structure.*.breaktitle"=>"nullable",
        ];
    }
}
