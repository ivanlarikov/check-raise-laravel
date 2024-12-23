<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class PopupSearchRequest extends FormRequest
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
            'language' => 'required|string',
            'popup_key' => 'required|string',
        ];
    }
}
