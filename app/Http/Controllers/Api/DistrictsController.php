<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\District\DistrictResource;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DistrictsController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentId = $request->input('parent_id', 0);
        $districts = District::query()->where('parent_id', $parentId)->get();
        DistrictResource::wrap('data');
        return DistrictResource::collection($districts);
    }
}
