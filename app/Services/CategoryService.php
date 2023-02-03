<?php

namespace App\Services;

use App\Imports\CategoriesImport;
use App\Models\Category;
use App\Models\File;
use App\Models\Language;
use App\Models\Products;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CategoryService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Category())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            });
            $query->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%")
                    ->orWhere('slug', 'like', "%$request->search%");
            });

            $results = $query->select('categories.*')->with('image', 'translates')->paginate($perPage, ['*'], 'page', $page);

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
                if (!empty($data['description_en']))
                    $data['description'] = $data['description_en'];

                $data['name'] = $data['name_en'];

                $category = Category::create($data);
                $translateModels = array();
                $languages = Language::pluck('locale', 'id');
                foreach ($languages as $key => $locale) {
                    if (!empty($data['name_' . $locale])) {
                        $translateModel = new Translation([
                            'language_id' => $key,
                            'field_name' => 'name',
                            'translation' => $data['name_' . $locale],
                        ]);
                        array_push($translateModels, $translateModel);
                    }
                    if (!empty($data['description_' . $locale])) {
                        $translateModel = new Translation([
                            'language_id' => $key,
                            'field_name' => 'description',
                            'translation' => $data['description_' . $locale],
                        ]);
                        array_push($translateModels, $translateModel);
                    }
                }
                $category->translates()->saveMany($translateModels);

                //saving image data
                if (!empty($data['file'])) {
                    $path = Storage::putFile('public/category', $data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $category->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['Category created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function update($category, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($category, $data) {

                if (!empty($data['description_en']))
                    $data['description'] = $data['description_en'];

                if (!empty($data['name_en']))
                    $data['name'] = $data['name_en'];

                $category->update($data);
                $languages = Language::pluck('locale', 'id');
                foreach ($languages as $key => $locale) {
                    if (!empty($data['name_' . $locale])) {
                        $category->translates()->whereLanguageId($key)->whereFieldName('name')->updateOrCreate([
                            'language_id' => $key,
                            'field_name' => 'name',
                            'translation' => $data['name_' . $locale],
                        ]);
                    }

                    if (!empty($data['description_' . $locale])) {
                        $category->translates()->whereLanguageId($key)->whereFieldName('description')->updateOrCreate([
                            'language_id' => $key,
                            'field_name' => 'description',
                            'translation' => $data['description_' . $locale],
                        ]);
                    }
                }

                //update image data
                if (!empty($data['file'])) {

                    if ($category->image) {
                        $oldFile = $category->image->path;

                        if (Storage::exists($oldFile)) {
                            Storage::delete($oldFile);
                        }
                        $category->image->delete();
                    }
                    $path = Storage::putFile('public/category', $data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $category->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['Category updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function delete($category): JsonResponse
    {
        try {
            $category->delete();

            return response()->json([
                'messages' => ['Category deleted successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function import($request): JsonResponse
    {
        $import = new CategoriesImport;
        Excel::import($import, $request->file('upload_file'));
        return response()->json([
            'messages' => ['Excel uploaded successfully'],
            'total_success_upload' => $import->totalSuccessRecords,
            'total_fail_upload' => $import->totalFailRecords,
            'not_upload_data' => $import->notUploadData,
        ], 201);
    }
}
