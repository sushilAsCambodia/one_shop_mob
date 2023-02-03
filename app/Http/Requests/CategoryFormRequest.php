<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class CategoryFormRequest extends FormRequest
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
        $locales = Language::pluck('locale');
        $validation = [
            'slug' => $this->method() === 'POST' ? 'required|max:255|unique:categories,slug,NULL,id,deleted_at,NULL':
                                                    "unique:categories,slug,{$this->category->id},id,deleted_at,NULL",
            'status' => $this->method() === 'POST' ? 'required|in:active,inactive': 'in:active,inactive',
            'file' => $this->method() === 'POST' ? 'required|file': 'file',
        ];

        foreach( $locales as $locale){
            $nameValidation =  $this->method() === 'POST' ? 'required|max:255': 'max:255';
            $descriptionValidation =  $this->method() === 'POST' ? 'nullable': 'nullable';
            $validation['name_'.$locale] = $nameValidation;
            $validation['description_'.$locale] = $descriptionValidation;
        }
        return $validation;
    }
}
