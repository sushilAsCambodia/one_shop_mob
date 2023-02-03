<?php

namespace App\Http\Requests;

use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class PaymentFormRequest extends FormRequest
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
            '*.order_id' => $this->method() === 'POST' ? 'required|exists:orders,id': 'exists:orders,id',
            '*.order_product.*.order_product_id' => $this->method() === 'POST' ? 'required|exists:order_product,id': 'exists:order_product,id',

        ];
    }

    public function messages()
    {
        return [
        ];
    }

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        //remove lang_id
        $validateData = $this->all();
        unset($validateData['lang_id']);
        return $validateData;
    }
}
