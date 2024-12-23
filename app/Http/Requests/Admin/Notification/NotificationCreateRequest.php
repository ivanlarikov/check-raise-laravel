<?php

namespace App\Http\Requests\Admin\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationCreateRequest extends FormRequest
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
            'type' => 'required',
            'status' => 'required',
            'title' => 'required',
            'slug' => 'required',
            'content' => 'required',
        ];
    }
}
