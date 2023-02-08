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
            'street_address_1' => $this->method() === 'POST' ? 'required' : "",
            'street_address_2' => $this->method() === 'POST' ? 'nullable' : "",
            'country_id' => $this->method() === 'POST' ? 'required|exists:countries,id' : 'exists:countries,id',
            'state_id' => $this->method() === 'POST' ? 'required|exists:states,id' : "exists:states,id",
            'city_id' => $this->method() === 'POST' ? 'required|exists:cities,id' : "exists:cities,id",
            'pincode' => $this->method() === 'POST' ? 'required' : "",
        ];
    }

    public function messages()
    {
        return [
            'street_address_1.required' => 'address_line_1_is_required',
            'country_id.required' => 'country_id_is_required',
            'country_id.exists' => 'country_id_is_not_exists',
            'state_id.required' => 'state_id_is_required',
            'state_id.exists' => 'state_id_is_not_exists',
            'city_id.required' => 'city_id_is_required',
            'city_id.exists' => 'city_id_is_not_exists',
            'pincode.required' => 'pincode_is_required',
        ];
    }
}
