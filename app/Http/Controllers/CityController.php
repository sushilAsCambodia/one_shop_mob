<?php

namespace App\Http\Controllers;

use App\Http\Requests\CityFormRequest;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct()
    {
    }

    public function all($stateId)
    {
        $result['message'] = 'City_fetch_successfully';
        $result['data'] = City::where('state_id', $stateId)->where('status', 'active')->get();
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }
}
