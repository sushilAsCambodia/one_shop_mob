<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait DateSerializable
{
    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
