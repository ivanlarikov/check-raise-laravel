<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class ContactCreateRequest extends FormRequest
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
      'firstname' => 'required|string',
      'lastname' => 'required|string',
      'email' => 'required|email',
      'message' => 'required|string',
      'room' => 'required|string'
    ];
  }
}
