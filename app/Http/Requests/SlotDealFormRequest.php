<?php

namespace App\Http\Requests;

use App\Rules\ExternalSystemRoleRule;
use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class SlotDealFormRequest extends FormRequest
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
        //$emailRule = $this->method() === 'POST' ?
        // 'required|email|unique:users,email' : 'required|email|unique:users,email,'.$this->user->id;


        return [
            // 'translation' => 'array|required',
            // 'sku' => 'required|unique:products,sku',
            // 'quantity' => 'required',
            // 'slug' => 'required',
            // 'price' => 'array|required',
            // 'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            // 'name.required' => 'Product name is required',
            // 'sku.required' => 'Product sku is required',
            // 'sku.unique' => 'Product sku must be qunique',
        ];
    }
}
