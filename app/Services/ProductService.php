<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\File;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCurrency;
use App\Models\Promotion;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductService
{
    public function index($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 100;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';  //pice,most_view,popular
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
            $isSortByPrice = false; 
            $query = Product::distinct('products.id');
            // $tag = null;
            // $promotion = null;
            // switch ($request->tag) {
            //     case 'top_deals':
            //         $promotion = '2';
            //         $tag = 'top_deals';
            //         break;
            //     case 'hot_deals':
            //         $promotion = '1';
            //         $tag = 'hot_deals';
            //         break;
            //     case 'trending_now':
            //         $promotion = '3';
            //         $tag = 'trending_now';
            //         break;
            //     case 'latest':
            //         $tag = 'latest';
            //         break;
            // }
            if($request->tag){
                if($request->tag == 'latest'){
                    $query = Product::orderBy('created_at', 'desc');
                    
                }else{
                    $query->whereHas('promotion', function ($query) use ($request) {
                        $query->where('promotions.slug',$request->tag);
                    });
                }
            }
            if(in_array($sortBy, ['created_at', 'price','most_view','popular','latest'])){
            
                if($sortBy == 'popular')
                    $sortBy = 'views';
                else if($sortBy == 'most_view')
                    $sortBy = 'views';
                else if($sortBy == 'latest')
                    $sortBy = 'created_at';
                
                if($request->descending){
                    $isSortByPrice =  true;
                    $query->leftJoin('deals','deals.product_id','products.id')->orderBy('deals.deal_price',$sortOrder);
                }
                $query->orderBy('products.'.$sortBy, 'desc');

            }
            $query->when($request->category_id, function ($query) use ($request){
                $query->whereHas('category',function ($query) use ($request){
                    $query->whereId($request->category_id);
                });
            });
            $query->when($request->sub_category_id, function ($query) use ($request){
                $query->whereHas('subCategory',function ($query) use ($request){
                    $query->whereId($request->sub_category_id);
                });
            });
            $query->when($request->priceMin || $request->priceMax, function ($query) use ($request){
                $query->whereHas('deal',function ($query) use ($request){
                    if($request->priceMin && $request->priceMax)
                        $query->whereBetween('deals.deal_price',[(int)$request->priceMin , (int)$request->priceMax]);
                    else if($request->priceMin)
                        $query->where('deals.deal_price','>=',(int)$request->priceMin);
                    else if($request->priceMax)
                        $query->where('deals.deal_price','<=',(int)$request->priceMax);
                });
            });
            $query->whereHas('deals',function ($query){
                $query->whereIn('status',['expired', 'active']);
            });
            if($isSortByPrice)
                $query->select('products.*','deals.deal_price');
            else
                $query->select('products.*');

            $data = $query->paginate($perPage, ['*'], 'page', $page);

            $result['message'] = 'product_fetch_successfully';
            $result['data'] = $data;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Product())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });
            $query->whereHas('deals',function ($query){
                $query->whereIn('status',['expired', 'active']);
            });
            $results = $query->select('products.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($data) {
                $product = Product::create($data);
                $productId = $product->id;
                if (isset($data['images'])) {
                    foreach ($data['images'] as $image) {
                        $path = uploadImage($image, 'products');
                        $file = new File([
                            'path' => $path,
                            'type' => checkFileType($image)
                        ]);
                        $product->image()->save($file);
                    }
                }
                if (isset($data['price'])) {
                    foreach ($data['price'] as $price) {
                        $price['product_id'] = $productId;
                        ProductCurrency::create($price);
                    }
                }
                if (isset($data['translation'])) {
                    foreach ($data['translation'] as $translation) {
                        $transData = new Translation([
                            'language_id' => $translation['language_id'],
                            'field_name' => $translation['field_name'],
                            'translation' => $translation['value']
                        ]);
                        $product->translation()->save($transData);
                    }
                }

                if (isset($data['tags'])) {
                    foreach ($data['tags'] as $tag) {
                        $tagData = Tag::where('name', $tag)->first();
                        if (!$tagData) {
                            $tagData = Tag::create([
                                'name' => $tag
                            ]);
                        }
                        DB::table('product_tag')->insert([
                            'product_id' => $productId,
                            'tag_id' => $tagData->id,
                        ]);
                    }
                }
                if (isset($data['deals'])) {
                    $hotDeals = Promotion::whereName($data['deals'])->first();
                    if (!$hotDeals) {
                        $hotDeals = Promotion::create([
                            "name" => $data['deals']
                        ]);
                    }
                    DB::table('product_promotion')->insert([
                        'product_id' => $productId,
                        'promotion_id' => $hotDeals->id,
                    ]);
                }
                //update inventory
                $inventory = Inventory::whereProductId($productId)->first();
                if($inventory)
                    $inventory->update(['available_stock' => $inventory->available_stock+1]);
                else
                    Inventory::create([
                        'product_id' => $productId,
                        'available_stock' => 1,
                        'sku' => $data['sku']
                    ]);
            });

            return response()->json([
                'messages' => ['Product created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function update($product, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($product, $data) {
                $product->update($data);
            });

            return response()->json([
                'messages' => ['User updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function delete($product): JsonResponse
    {
        try {
            $product->delete();
            $inventory = Inventory::whereProductId($product->id)->first();
            if($inventory)
                $inventory->update(['available_stock' => $inventory->available_stock?$inventory->available_stock-1:$inventory->available_stock]);

            return response()->json([
                'messages' => ['Product deleted successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getByCategorySlug( $request, $slug): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;

            $query = (new Product())->newQuery();

            $query->where(function ($query) use ($slug) {
                $query->whereHas('category', function ($query) use ($slug){
                            $query->where('slug', $slug);
                        })
                        ->orWhereHas('subCategory', function ($query) use ($slug){
                            $query->where('slug', $slug);
                        });
            });
            $results = $query->select('products.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

}
