<?php

namespace App\Http\Controllers;

use App\Http\Requests\StateFormRequest;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct()
    {
    }

    public function all()
    {
        return response()->json(State::all(), 200);
    }

    public function getById($counteyId)
    {
        $result['message'] = 'State_fetch_successfully';
        $result['data'] = State::where('country_id', $counteyId)->where('status', 'active')->get();
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }
}
