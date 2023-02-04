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
        $result['message'] = 'Country_fetch_successfully';
        $result['data'] = Country::where('status', 'active')->get();
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }
}
