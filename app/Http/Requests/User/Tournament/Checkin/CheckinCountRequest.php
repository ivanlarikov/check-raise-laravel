<?php

namespace App\Http\Requests\User\Tournament\Checkin;

use Illuminate\Foundation\Http\FormRequest;

class CheckinCountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'tournament_id'=>'required',
            'maxplayers'=>'required',
            'reservedplayers'=>'required'
        ];
    }
}
