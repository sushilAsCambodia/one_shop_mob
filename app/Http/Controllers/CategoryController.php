<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryFormRequest;
use App\Http\Requests\CategoryImportRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use App\Imports\CategoriesImport;
use Maatwebsite\Excel\Facades\Excel;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->categoryService->paginate($request);
    }

    public function all()
    {
        $result['message'] = 'Categories_fetch_successfully';
        $result['data'] = Category::all();
        $result['statusCode'] = 200;
        return getSuccessMessages($result);
    }

    public function treeView()
    {
        $result['message'] = 'Category_subCategory_fetch_successfully';
        $result['data'] = Category::with('subCategories')->get();
        $result['statusCode'] = 200;

        return getSuccessMessages($result);
    }

    public function store(CategoryFormRequest $request)
    {

        return $this->categoryService->store($request->all());
    }

    public function update(CategoryFormRequest $request, Category $category)
    {
        return $this->categoryService->update($category, $request->all());
    }

    public function delete(Category $category)
    {
        return $this->categoryService->delete($category);
    }

    public function upload(CategoryImportRequest $request)
    {
        return $this->categoryService->import($request);
    }

}