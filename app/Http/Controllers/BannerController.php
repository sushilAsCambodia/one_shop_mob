<?php

namespace App\Http\Controllers;

use App\Http\Requests\BannerFormRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->bannerService->paginate($request);
    }

    public function all(Request $request)
    {
        return response()->json(Banner::where('type',$request->type)->get(), 200);
    }

    public function store(BannerFormRequest $request)
    {

        return $this->bannerService->store($request->all());
    }

    public function update(BannerFormRequest $request, Banner $banner)
    {
        return $this->bannerService->update($banner, $request->all());
    }

    public function delete(Banner $banner)
    {
        return $this->bannerService->delete($banner);
    }
}
