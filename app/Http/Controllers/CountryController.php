<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountryFormRequest;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function __construct()
    {
    }

    public function all()
    {
        return response()->json(Country::where('status','active')->get(), 200);
    }
}
