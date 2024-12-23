<?php

namespace App\Http\Requests\User\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
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
   */
  public function rules(): array
  {

    return [
      'email' => 'nullable',
      'firstname' => 'required',
      'lastname' => 'required',
      'dob' => 'required|date|before:-18 years',
      'street' => 'required',
      'language' => 'required|max:10',
      'nickname' => 'nullable',
      'city' => 'required',
      'zipcode' => 'required|max:10',
      'displayoption' => 'nullable|max:10',
      'phonecode' => 'required|max:10',
      'phonecountry' => 'required|max:10',
      'phonenumber' => 'required|max:10',
      'newpassword' => 'nullable'
    ];
  }
}
