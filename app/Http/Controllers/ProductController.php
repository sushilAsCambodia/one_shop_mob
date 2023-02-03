<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFormRequest;
use App\Http\Requests\ProductImportRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;



class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->productService->paginate($request);
    }

    public function index(Request $request)
    {
        return $this->productService->index($request);
    }

    public function all()
    {
        return response()->json(Product::all(), 200);
    }

    public function store(ProductFormRequest $request)
    {
        return $this->productService->store($request->all());
    }

    public function update(ProductFormRequest $request, Product $product)
    {
        return $this->productService->update($product, $request->all());
    }

    public function delete(Product $product)
    {
        return $this->productService->delete($product);
    }

    public function get(Product $product)
    {

        $product->update(['views' => $product->views + 1]);

        return response()->json($product, 200);
    }
    public function upload(ProductImportRequest $request)
    {
        return $this->productService->import($request);
    }

    public function getByCategorySlug(Request $request, $slug)
    {
        return $this->productService->getByCategorySlug($request, $slug);
    }
}
