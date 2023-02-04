<?php

namespace App\Http\Requests;

use App\Traits\PhoneNumberSerializable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Traits\FailedValidation;

class CustomerForgetPasswordFormRequest extends FormRequest
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
            'phone_number' => ['required',
                Rule::exists('customers')->where(function ($query) {
                    return $query->where('phone_number', $this->phone_number)
                                ->where('idd', $this->idd)
                                ->where('deleted_at', NULL);
                }),
            ],
        ];
    }
    public function messages()
    {
        return [
            'phone_number.exists' => 'Phone_Number_Not_Registered',
            'idd.required' => 'idd_is_required',
            'phone_number.required' => 'phone_number_is_required',
        ];
    }

}
