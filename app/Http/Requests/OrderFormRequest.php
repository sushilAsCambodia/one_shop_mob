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
            "product_details.required" => "Cart is Empty",
            'product_details.array' => 'The product details must be an array.',
            'product_details.min' => 'The product details must have at least one item.',
            'product_details.*.product_id.required' => 'The product ID field is required.',
            'product_details.*.product_id.exists' => 'The selected product is invalid, or the deal of this product not available',
            'product_details.*.amount.required' => 'The amount field is required.',
            'product_details.*.amount.int' => 'The amount must be an integer.',
            'product_details.*.slots.required' => 'The slots field is required.',
            'product_details.*.slots.int' => 'The slots must be an integer.',
        ];
    }
}
