<?php

namespace App\Http\Requests;

use App\Traits\PhoneNumberSerializable;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class CustomerPasswordFormRequest extends FormRequest
{
    use PhoneNumberSerializable, FailedValidation;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'idd' => 'required',
            'phone_number' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function messages()
    {
        return [
            'idd.required' => 'idd_is_required',
            'phone_number.required' => 'phone_number_is_required',
            'password.required' => 'password_is_required',
            'password_confirmation.required' => 'password_confirmation_is_required',
            'password_confirmation.same' => 'password_confirmation_is_same',
        ];
    }
}
