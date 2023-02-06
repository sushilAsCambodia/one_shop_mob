<?php

namespace App\Http\Requests;

use App\Rules\ExternalSystemRoleRule;
use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class OrderFormRequest extends FormRequest
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
            'product_details' => 'required|array|min:1',
            'product_details.*.product_id' => $this->method() === 'POST' ? 'required|exists:products,id': 'exists:products,id',
            'product_details.*.product_id' => $this->method() === 'POST' ? 'required|exists:deals,product_id,status,active': 'exists:deals,product_id,status,active',
            'product_details.*.amount' => $this->method() === 'POST' ? 'required|int': '',
            'product_details.*.slots' => $this->method() === 'POST' ? 'required|int': '',
        ];
    }

    public function messages()
    {
        return [
            "product_details.required" => "Cart_is_Empty",
            'product_details.array' => 'The_product_details_must_be_an_array',
            'product_details.min' => 'The_product_details_must_have_at_least_one_item',
            'product_details.*.product_id.required' => 'The_product_ID_field_is_required',
            'product_details.*.product_id.exists' => 'The_selected_product_is_invalid_or_the_deal_of_this_product_not_available',
            'product_details.*.amount.required' => 'The_amount_field_is_required',
            'product_details.*.amount.int' => 'The_amount_must_be_an_integer',
            'product_details.*.slots.required' => 'The_slots_field_is_required',
            'product_details.*.slots.int' => 'The_slots_must_be_an_integer',
        ];
    }
}
