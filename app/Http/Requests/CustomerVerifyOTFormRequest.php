<?php

namespace App\Http\Requests;

use App\Traits\PhoneNumberSerializable;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class CustomerVerifyOTFormRequest extends FormRequest
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
            'phone_number' => 'required',
            'idd' => 'required',
            'otp' => 'required',
            'type' => 'in:register,forget_password',
        ];
    }

    public function messages()
    {
        return [
            'idd.required' => 'idd_required',
            'otp.required' => 'otp_required',
            'phone_number.required' => 'number_required',
        ];
    }
}
