<?php

namespace App\Http\Controllers\Ios;

use App\Http\Requests\Ios\OnetimePasswordFormRequest;
use App\Http\Requests\Ios\OnetimePasswordMailFormRequest;
use App\Http\Requests\Ios\OnetimePasswordForgatePassFormRequest;
use App\Models\Language;
use App\Services\Ios\HomepageControllerService;
use Illuminate\Http\Request;

class HomepageController extends \App\Http\Controllers\Controller
{
    public function __construct(private HomepageControllerService $homepageControllerService)
    {
    }

    public function getHomePageData(Request $request)
    {
        return $this->homepageControllerService->getHomePageData($request);
    }

    public function getlanguagesAll(Request $request)
    {
        return $this->homepageControllerService->getlanguagesAll($request);
    }

    public function getCategoriesAll(Request $request)
    {
        return $this->homepageControllerService->getCategoriesAll($request);
    }

    public function getBannersAll(Request $request)
    {
        return $this->homepageControllerService->getBannersAll($request);
    }

    public function getPromotional(Request $request)
    {
        return $this->homepageControllerService->getPromotional($request);
    }

    public function countriesAll()
    {
        return $this->homepageControllerService->countriesAll();
    }

}
