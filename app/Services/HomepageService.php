<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Currency;
use App\Models\homePage;
use App\Models\Language;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class HomepageService
{
    public function index($request): JsonResponse
    {
        try {
            Session::put("filter_slot_by_status", true);
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            // $data['category'] = Category::with('subCategories')->orderBy('name', 'asc')->get();
            // $data['languages'] = Language::orderBy('name', 'asc')->get();
            // $data['currency'] = Currency::orderBy('id', 'asc')->get();
            $data = [];
            $promotions = Promotion::all();
            foreach ($promotions as $promotion) {
                $data[$promotion->slug] =     $this->getData($sortBy, $sortOrder, $promotion->slug);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getData($sortBy, $sortOrder, $slug)
    {
        $products = Product::whereHas('deals', function ($query) {
                $query->whereIn('status', ['expired', 'active']);
            });
        
        $products = $products->with([
            'image',
            'translation',
            'tags',
            'deal.slots',
            'favouriteCount',
        ]);

        return $products->select('products.*')->inRandomOrder()->limit(8)->orderBy($sortBy, $sortOrder)->get();
    }

    public function getSearchResult($request): JsonResponse
    {
        try {
            if (!isset($request->type)) {
                $request->type = "product";
            }
            $data = [];
            $categories = [];
            switch ($request->type) {
                case 'category':
                    $query = (new Category())->newQuery();
                    $query->when($request->search_key, function ($q) use ($request) {
                        $q->where(function ($q) use ($request) {
                            $q->where('slug', 'like', "%$request->search_key%")
                                ->orWhereHas('translates', function ($q) use ($request) {
                                    $q->where('translation', 'like', "%$request->search_key%");
                                })
                                ->orWhereHas('subCategories', function ($q) use ($request) {
                                    $q->where('slug', 'like', "%$request->search_key%")
                                        ->orWhereHas('translates', function ($q) use ($request) {
                                            $q->where('translation', 'like', "%$request->search_key%");
                                        });
                                });
                        });
                    });
                    $data = $query->with('subCategories')->select('categories.*')->get();

                    break;

                case 'product':
                    $query = (new Product())->newQuery();

                    $query->when($request->search_key, function ($q) use ($request) {
                        $q->whereHas('translation', function ($q) use ($request) {
                            $q->where('field_name', 'name')
                                ->where('translation', 'like', "%$request->search_key%");
                        });
                    });
                    $query->whereHas('deals', function ($query) {
                        $query->whereIn('status', ['expired', 'active']);
                    });
                    $data = $query->select('products.*')->get();

                    //query category and sub category
                    // if ($request->search_key) {
                    //     $query = (new Category())->newQuery();
                    //     $query->where(function ($q) use ($request) {
                    //         $q->whereHas('translates', function ($q) use ($request) {
                    //             $q->where('translation', 'like', "%$request->search_key%");
                    //         });
                    //     });
                    //     $categories = $query->select('categories.*')->get()->toArray();

                    //     $query = (new SubCategory())->newQuery();
                    //     $query->where(function ($q) use ($request) {
                    //         $q->whereHas('translates', function ($q) use ($request) {
                    //             $q->where('translation', 'like', "%$request->search_key%");
                    //         });
                    //     });
                    //     $subCategories = $query->select('sub_categories.*')->get()->toArray();
                    //     $categories = array_merge($categories, $subCategories);
                    // }

                    break;
            }

            $result['message'] = 'fetch_search_data_successfully';
            $result['data'] = $data;

            $result['statusCode'] = 200;

            return getSuccessMessages($result);            
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getPromotional($request)
    {
        try {
            Session::put("promotional_query_session", true);

            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $promotions = Promotion::whereStatus('active')->paginate($perPage, ['*'], 'page', $page);
            // $promotions->map(function ($item) use ($sortBy, $sortOrder){
            //     // modify the item here
            //     $item->products = $this->getData($sortBy, $sortOrder, $item->slug);
            //     return $item;
            // });
            $data = [];
            foreach ($promotions as $promotion) {
                $data[] = [
                    'id'        =>     $promotion->id,
                    'name'      =>     $promotion->name,
                    'products'  =>     $this->getData($sortBy, $sortOrder, $promotion->slug),
                    'slug'      =>     $promotion->slug,
                    'image'      =>     $promotion->image
                ];
            }
            // $paginator = new LengthAwarePaginator($promotions->all(), $promotions->count(), $perPage);

            $result['message'] = 'fetch_all_homePage_successfully';
            $result['data'] = [
                'languages' => Language::all(),
                'banners' => Banner::where('type', 'homePage')->get(),
                'categories' => Category::all(),
                'promotional' => $data,
            ];
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
