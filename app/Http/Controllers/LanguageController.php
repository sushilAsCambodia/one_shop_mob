<?php

namespace App\Http\Controllers;

use App\Http\Requests\LanguageFormRequest;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct(private LanguageService $languageService)
    {
    }

    public function store(LanguageFormRequest $request)
    {
        return $this->languageService->store($request->all());
    }

    public function update(LanguageFormRequest $request, Language $language)
    {
        return $this->languageService->update($language, $request->all());
    }

    public function delete(Language $language)
    {
        return $this->languageService->delete($language);
    }

    public function get(Language $language)
    {
        return response()->json($language, 200);
    }

    public function all()
    {
        $result['message'] = 'languages_fetch_successfully';
        $result['data'] = ['languages' => Language::all()];
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }

    public function paginate(Request $request)
    {
        return $this->languageService->paginate($request);
    }
}
