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
        return response()->json(City::where('state_id', $stateId)->where('status','active')->get(), 200);
    }
}
