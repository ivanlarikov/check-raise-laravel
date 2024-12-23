<?php

namespace App\Http\Requests\User\Registration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PlayerRegistrationRequest extends FormRequest
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
      'username' => 'required',
      'email' => 'required|unique:users,email',
      'password' => 'required',
      'firstname' => 'required',
      'lastname' => 'required',
      'dob' => 'required|date|before:-18 years',
      'street' => 'required',
      'language' => 'required|max:10',
      'city' => 'required',
      'zipcode' => 'required|max:10',
      'nickname' => 'nullable',
      'phonecode' => 'required',
      'displayoption' => 'nullable',
      'phonecountry' => 'required|max:10',
      'phonenumber' => 'required|max:10',

    ];
  }
}
