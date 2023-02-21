<?php

namespace App\Http\Requests;

use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class PaymentResponseFormRequest extends FormRequest
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
            'status' => 'required|in:success,fail',
            'external_order_ID' => 'required|exists:payments,external_order_ID',

        ];
    }

    public function messages()
    {
        return [
            'status.required'=>'status_required',
            'external_order_ID.required'=>'external_order_ID_not_required',
            'external_order_ID.exists'=>'external_order_ID_not_exists',
            // 'order_id.required'=>'order_id_is_required',
            // 'order_id.exists'=>'order_id_is_not_exists',
            // 'order_product_id.required'=>'order_product_id_is_required',
            // 'order_product_id.exists'=>'order_product_id_is_not_exists',
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
