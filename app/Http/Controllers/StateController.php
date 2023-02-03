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
        return response()->json(State::where('country_id', $counteyId)->where('status','active')->get(), 200);
    }
}
