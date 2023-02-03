<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryFormRequest extends FormRequest
{
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
            'product_id' => $this->method() === 'POST' ? 'required' : '',
            'sku' => $this->method() === 'POST' ? 'required' : '',
            'available_stock' => $this->method() === 'POST' ? 'required|int' : '',
            'status' => 'in:active,inactive',

        ];
    }
}
