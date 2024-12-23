<?php

namespace App\Http\Requests\Admin\Room;

use Illuminate\Foundation\Http\FormRequest;

class RoomStatusUpdateRequest extends FormRequest
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
            'id'    =>'required',
            'status'=>'required'
        ];
    }
}
