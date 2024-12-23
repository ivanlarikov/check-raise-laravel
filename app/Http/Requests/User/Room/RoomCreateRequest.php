<?php

namespace App\Http\Requests\User\Room;

use Illuminate\Foundation\Http\FormRequest;

class RoomCreateRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'title' => 'required|string:rooms,title',
            'slug' => 'required|string:rooms,slug',
            'user_id' => 'required:rooms,user_id',
            'language' => 'required|string',
            'description' => 'required|string',
            'logo' => 'required|string',
            'street' => 'required|string',
            'town' => 'required|string',
            'canton' => 'required|string',
            'phone' => 'required|max:10',
            'phonecode' => 'required|string',
            'phonecountry' => 'required|string',
            'website' => 'required|string',
            'contact' => 'required|string',
            'city' => 'required|string',
            'zipcode' => 'required|string',
        ];
    }
}
