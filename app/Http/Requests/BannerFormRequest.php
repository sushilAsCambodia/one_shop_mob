<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class BannerFormRequest extends FormRequest
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
            'slug' => $this->method() === 'POST' ? 'required|max:255|unique:banners,slug,NULL,id,deleted_at,NULL':
                                                    "unique:banners,slug,{$this->banner->id},id,deleted_at,NULL",
            'status' => $this->method() === 'POST' ? 'required|in:active,inactive': 'in:active,inactive',
            'position' => $this->method() === 'POST' ? 'int': 'int',
            'link' => $this->method() === 'POST' ? 'nullable': 'nullable',
            'file' => $this->method() === 'POST' ? 'required|file': 'file',
        ];
        foreach( $locales as $locale){
            $nameValidation =  $this->method() === 'POST' ? 'required|max:255': 'max:255';
            $descriptionValidation =  $this->method() === 'POST' ? 'nullable': 'nullable';
            $contentValidation =  $this->method() === 'POST' ? 'nullable': 'nullable';
            $validation['name_'.$locale] = $nameValidation;
            $validation['description_'.$locale] = $descriptionValidation;
            $validation['content_'.$locale] = $contentValidation;
        }
        return $validation;
    }
}
