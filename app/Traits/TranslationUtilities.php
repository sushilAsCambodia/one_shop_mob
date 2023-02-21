<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait TranslationUtilities
{
    public function getStatusAttribute($attribute){
        // return trans('message.'.$attribute);
        return $attribute;
    }

    public function setStatusAttribute($attribute){
        //return trans('message.'.$attribute,[],'en');
        return $attribute;
    }
}
