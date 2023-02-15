<?php

namespace App\Http\Requests;

use App\Traits\PhoneNumberSerializable;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class CustomerLoginFormRequest extends FormRequest
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
            'password' => 'required',
        ];

    }

    public function messages()
    {
        return [
            'idd.required' => 'idd_required',
            'phone_number.required' => 'number_required',
            'password.required' => 'password_required',
        ];
    }
}
