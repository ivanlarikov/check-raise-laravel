<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'id'=>'required',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ];
    }
}
