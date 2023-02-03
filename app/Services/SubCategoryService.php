<?php

namespace App\Services;

use App\Models\File;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubCategoryService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new SubCategory())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            });
            $query->when($request->category_id, function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            });
            $query->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%")
                        ->orWhere('slug', 'like', "%$request->search%");
            });

            $results = $query->select('sub_categories.*')->with('image')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($data) {
                $subCategory = SubCategory::create($data);

                //saving image data
                if(!empty($data['file'])) {
                    $path = Storage::putFile('public/sub-category',$data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $subCategory->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['SubCategory created successfully'],
            ], 201);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function update($subCategory, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($subCategory, $data) {
                $subCategory->update($data);
                //update image data
                if(!empty($data['file'])) {

                    if($subCategory->image){
                        $oldFile = $subCategory->image->path;
                        if(Storage::exists($oldFile)) {
                            Storage::delete($oldFile);
                        }
                        $subCategory->image->delete();
                    }

                    $path = Storage::putFile('public/sub-category',$data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $subCategory->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['SubCategory updated successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($subCategory): JsonResponse
    {
        try {
            $subCategory->delete();

            return response()->json([
                'messages' => ['SubCategory deleted successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

}
