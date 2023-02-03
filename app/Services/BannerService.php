<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\File;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Banner())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            });
            $query->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%")
                        ->orWhere('slug', 'like', "%$request->search%");
            });

            $results = $query->select('banners.*')->with('image')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }
    
    public function store(array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($data) {
                if(!empty($data['description_en']))
                    $data['description'] = $data['description_en'];

                $data['name'] = $data['name_en'];

                $banner = Banner::create($data);
                $translateModels = array();
                $languages = Language::pluck('locale','id');
                foreach($languages as $key => $locale){
                    if(!empty($data['name_'.$locale])){
                        $translateModel = new Translation([
                            'language_id' => $key,
                            'field_name' => 'name',
                            'translation' => $data['name_'.$locale],
                        ]);
                        array_push($translateModels,$translateModel);
                    }
                    if(!empty($data['description_'.$locale])){
                        $translateModel = new Translation([
                            'language_id' => $key,
                            'field_name' => 'description',
                            'translation' => $data['description_'.$locale],
                        ]);
                        array_push($translateModels,$translateModel);
                    }
                    if(!empty($data['content_'.$locale])){
                        $translateModel = new Translation([
                            'language_id' => $key,
                            'field_name' => 'content',
                            'translation' => $data['content_'.$locale],
                        ]);
                        array_push($translateModels,$translateModel);
                    }
                }
                $banner->translates()->saveMany($translateModels);

                //saving image data
                if(!empty($data['file'])) {
                    $path = Storage::putFile('public/banner',$data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $banner->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['Banner created successfully'],
            ], 201);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function update($banner, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($banner, $data) {

                if(!empty($data['description_en']))
                    $data['description'] = $data['description_en'];

                if(!empty($data['name_en']))
                    $data['name'] = $data['name_en'];

                if(!empty($data['content_en']))
                    $data['content'] = $data['content_en'];

                $banner->update($data);
                $languages = Language::pluck('locale','id');
                foreach($languages as $key => $locale){
                    if(!empty($data['name_'.$locale])){
                        $banner->translates()->whereLanguageId($key)->whereFieldName('name')->updateOrCreate([
                            'translation' => $data['name_'.$locale],
                            'language_id' => $key,
                            'field_name' => 'name',
                        ]);
                    }

                    if(!empty($data['description_'.$locale])){
                        $banner->translates()->whereLanguageId($key)->whereFieldName('description')->updateOrCreate([
                            'translation' => $data['description_'.$locale],
                            'language_id' => $key,
                            'field_name' => 'description',
                        ]);
                    }

                    if(!empty($data['content_'.$locale])){
                        $banner->translates()->whereLanguageId($key)->whereFieldName('content')->updateOrCreate([
                            'translation' => $data['content_'.$locale],
                            'language_id' => $key,
                            'field_name' => 'content',
                        ]);
                    }
                }
                //update image data
                if(!empty($data['file'])) {

                    if($banner->image){
                        $oldFile = $banner->image->path;

                        if(Storage::exists($oldFile)) {
                            Storage::delete($oldFile);
                        }
                        $banner->image->delete();
                    }
                    $path = Storage::putFile('public/banner',$data['file']);
                    $file = new File([
                        'path' => $path,
                        'type' => checkFileType($data['file'])
                    ]);
                    $banner->image()->save($file);
                }
            });

            return response()->json([
                'messages' => ['Banner updated successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($banner): JsonResponse
    {
        try {
            $banner->delete();

            return response()->json([
                'messages' => ['Banner deleted successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

}
