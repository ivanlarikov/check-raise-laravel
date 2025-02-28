<?php

namespace App\Http\Requests\User\Tournament;

use Illuminate\Foundation\Http\FormRequest;

class TournamentDuplicateRequest extends FormRequest
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
            'tournament.title'=>'required',
            'tournament.original_id'=>'required',

            'detail.startday'  =>  'required',
            'detail.lastday'  =>  'nullable',
            'detail.bounusdeadline'  =>  'nullable',
        ];
    }
}
