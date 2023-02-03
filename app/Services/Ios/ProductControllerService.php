<?php

namespace App\Services\Ios;

use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\File;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCurrency;
use App\Models\Promotion;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\ProductService;
use App\Services\SubCategoryService;
use Maatwebsite\Excel\Facades\Excel;

class ProductControllerService
{
    public function __construct(private ProductService $productService, private SubCategoryService $subCategoryService)
    {
    }

    public function index($request): JsonResponse
    {
        try {
            $response = $this->productService->index($request);

            $result['message'] = 'product_fetch_successfully';
            $result['data'] = $response->original;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }


    public function treeView(): JsonResponse
    {
        try {

            $result['message'] = 'product_fetch_successfully';
            $result['data'] = Category::with('subCategories')->get();
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
