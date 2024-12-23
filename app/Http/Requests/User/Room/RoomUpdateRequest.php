<?php

namespace App\Http\Requests\User\Room;

use Illuminate\Foundation\Http\FormRequest;

class RoomUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    // public function rules(): array
    public function rules()
    {
        return [
            'room.id'       =>'required',
            'room.title'         =>'required',
			
            "details.logo"  =>"nullable",
            "details.street"        =>"required",
            "details.zipcode"       =>"required",
            "details.town"          =>"nullable",
            "details.city"          =>"required",
            "details.canton"        =>"required",
            "details.contact"       =>"nullable",
            "details.phone"         => "nullable",
            "details.phonecode"     => "nullable",
            "details.phonecountry"  => "nullable",
            "details.website"       => "nullable",
            "details.activelanguages"=> "nullable",
            "descriptions.*.language" => "required",
            "descriptions.*.description" => "nullable",
        ];
    }
}
