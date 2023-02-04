<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\FailedValidation;

class FavoriteFormRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
        ];
        return $validation;
    }
    public function messages()
    {
        return [
            'product_id.required' => 'product_id_is_required',
            'product_id.exists' => 'product_id_not_exists',
        ];
    }
}
