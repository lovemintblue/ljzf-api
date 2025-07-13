<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\District\DistrictInfoResource;
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

    /**
     * 根据名称获取下级列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getChildrenByName(Request $request): AnonymousResourceCollection
    {
        $name = $request->input('name');
        $parent = District::query()->whereLike('full_name', '%' . $name . '%')->first();
        $districts = District::query()->where('parent_id', $parent->id)->get();
        DistrictResource::wrap('data');
        return DistrictResource::collection($districts);
    }
}
