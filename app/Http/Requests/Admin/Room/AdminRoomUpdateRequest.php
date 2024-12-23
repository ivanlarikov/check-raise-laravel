<?php

namespace App\Http\Requests\Admin\Room;

use Illuminate\Foundation\Http\FormRequest;

class AdminRoomUpdateRequest extends FormRequest
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
            'room.id'                           =>'required',
            'room.title'                        =>'required',

            'room.expiry'                       => 'required',
            'room.credits'                      => 'required',
            'room.buyuinlimit'                  => 'required',
            'room.buy_in_limit_without_reentry' => 'required',
            'room.maxnumberoftopbanner'        => 'required',
			'room.maxnumberofbottombanner'        => 'required',
			'room.maxnumberoftournament'        => 'required',
            'room.maxnumberofpremium'           => 'required',
            'room.latearrivaldelay'             => 'required',

            "details.logo"                      =>"nullable",
            "details.street"                    =>"required",
            "details.zipcode"                   =>"required",
            "details.town"                      =>"nullable",
            "details.city"                      =>"required",
            "details.canton"                    =>"required",
            "details.contact"                   =>"nullable",
            "details.phone"                     => "nullable",
            "details.phonecode"                 => "nullable",
            "details.phonecountry"              => "nullable",
            "details.website"                   => "nullable",
            "details.activelanguages"           => "nullable",

            "descriptions.*.language"           => "nullable",
            "descriptions.*.description"        => "nullable",
        ];
    }
}
