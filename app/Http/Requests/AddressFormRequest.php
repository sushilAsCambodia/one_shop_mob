<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class AddressFormRequest extends FormRequest
{
    use FailedValidation;
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
            'country_id' => $this->method() === 'POST' ? 'required|exists:countries,id': 'exists:countries,id',
            'city_id' => $this->method() === 'POST' ? 'required|exists:cities,id': "exists:cities,id",
            'state_id' => $this->method() === 'POST' ? 'required|exists:states,id': "exists:states,id",
            'firstname' => $this->method() === 'POST' ? 'required': "",

            'lastname' => $this->method() === 'POST' ? 'required': "",
            'street_address_1' => $this->method() === 'POST' ? 'required': "",
            'street_address_2' => $this->method() === 'POST' ? 'nullable': "",
            'pincode' => $this->method() === 'POST' ? 'required': "",
            'phone' => $this->method() === 'POST' ? 'required': "",
            'email' => $this->method() === 'POST' ? 'required': "",
            'type' => $this->method() === 'POST' ? 'required|in:billing,shipping': "",

        ];
    }
}
