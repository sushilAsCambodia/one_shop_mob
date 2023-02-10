<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubCategoryFormRequest;
use App\Models\SubCategory;
use App\Models\Category;
use App\Services\SubCategoryService;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function __construct(private SubCategoryService $subCategoryService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->subCategoryService->paginate($request);
    }

    public function all()
    {
        return response()->json(SubCategory::all(), 200);
    }

    public function subCategoriesId(Request $request)
    {
        $result['message'] = 'Categories_fetch_successfully';
        $result['data'] = [
            'category' => Category::where('id', $request->categoryId)->first(),
            'subCategories' => SubCategory::with('product')
                               ->where('category_id', $request->categoryId)->get(),
        ];
        // Category::with('subCategories')->where('id', $categoryId)->first();
        // SubCategory::where('category_id', $categoryId)->get();
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }

// public function get(Request $request, SubCategory $subCategory)
// {
//     return response()->json(SubCategory::whereId($subCategory->id)->with('subCategories')->firstOrFail(), 200);
// }
}