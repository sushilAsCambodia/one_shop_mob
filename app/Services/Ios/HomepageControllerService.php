<?php

namespace App\Services\Ios;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Country;
use App\Models\Language;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\HomepageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class HomepageControllerService
{
    public function __construct(private HomepageService $homepageService)
    {
    }


    public function getHomePageData($request): JsonResponse
    {
        try {
            $response = $this->homepageService->getPromotional($request);

            $result['message'] = 'fetch_all_homePage_successfully';
            $result['data'] = [
                'languages' => Language::all(),
                'banners' => Banner::where('type', 'homePage')->get(),
                'categories' => Category::all(),
                'promotional' => $response->original['promotional'],
            ];

            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }
    public function getlanguagesAll($request): JsonResponse
    {
        try {

            $result['message'] = 'languages_fetch_successfully';
            $result['data'] = ['languages' => Language::all()];
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function getCategoriesAll($request): JsonResponse
    {
        try {
            $result['message'] = 'categories_fetch_successfully';
            $result['data'] = ['categories' => Category::all()];
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function getBannersAll($request): JsonResponse
    {
        try {

            $result['message'] = 'banner_fetch_successfully';
            $result['data'] = ['banners' => Banner::where('type', $request->type)->get()];
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function getPromotional($request)
    {
        try {
            $response = $this->homepageService->getPromotional($request);

            $result['message'] = 'promotional_fetch_successfully';
            $result['data'] = ['promotional' => $response->original['promotional']];
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function countriesAll()
    {
        try {
            $result['message'] = 'country_fetch_successfully';
            $result['data'] = Country::where('status', 'active')->get();
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }
}
