<?php

namespace App\Http\Controllers\Ios;

use App\Http\Requests\ProductFormRequest;
use App\Http\Requests\ProductImportRequest;
// use App\Models\Category;
// use App\Models\Product;
use App\Services\Ios\ProductControllerService;
use Illuminate\Http\Request;



class productController extends \App\Http\Controllers\Controller
{
    public function __construct(private productControllerService $productControllerService)
    {
    }

    public function index(Request $request)
    {
        return $this->productControllerService->index($request);
    }

    public function treeView()
    {
        return $this->productControllerService->treeView();
    }





    // public function paginate(Request $request)
    // {
    //     return $this->productControllerService->paginate($request);
    // }

    // public function all()
    // {
    //     return response()->json(Product::all(), 200);
    // }

    // public function store(ProductFormRequest $request)
    // {
    //     return $this->productControllerService->store($request->all());
    // }

    // public function update(ProductFormRequest $request, Product $product)
    // {
    //     return $this->productControllerService->update($product, $request->all());
    // }

    // public function delete(Product $product)
    // {
    //     return $this->productControllerService->delete($product);
    // }

    // public function get(Product $product)
    // {

    //     $product->update(['views' => $product->views + 1]);

    //     return response()->json($product, 200);
    // }
    // public function upload(ProductImportRequest $request)
    // {
    //     return $this->productControllerService->import($request);
    // }

    // public function getByCategorySlug(Request $request, $slug)
    // {
    //     return $this->productControllerService->getByCategorySlug($request, $slug);
    // }
}
