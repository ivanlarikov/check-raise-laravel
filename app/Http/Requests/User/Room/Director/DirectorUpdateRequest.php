<?php

namespace App\Http\Requests\User\Room\Director;

use Illuminate\Foundation\Http\FormRequest;

class DirectorUpdateRequest extends FormRequest
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
            'user_id'  => 'required',
            'password' => 'nullable',
            'capabilities'=>'required|array'
        ];
    }
}
