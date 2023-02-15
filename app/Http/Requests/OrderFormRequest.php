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
            'product_details.*.deal_id' => $this->method() === 'POST' ? 'required|exists:deals,id,status,active': 'exists:deals,id,status,active',
            'product_details.*.amount' => $this->method() === 'POST' ? 'required|int': '',
            'product_details.*.slots' => $this->method() === 'POST' ? 'required|int': '',
        ];
    }

    public function messages()
    {
        return [
            "product_details.required" => "cart_Empty",
            'product_details.array' => 'invalid_product',
            'product_details.min' => 'select_at_least_one_item',
            'product_details.*.deal_id.required' => 'product_required',
            'product_details.*.deal_id.exists' => 'invalid_product',

            'product_details.*.amount.required' => 'amount_required',
            'product_details.*.amount.int' => 'invalid_amount',
            'product_details.*.slots.required' => 'slots_required',
            'product_details.*.slots.int' => 'invalid_slots',
        ];
    }
}
