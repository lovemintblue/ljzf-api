<?php
/**
 * 房源 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HouseRequest;
use App\Http\Resources\House\HouseInfoResource;
use App\Http\Resources\House\HouseResource;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HousesController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $houses = House::query()->latest()->paginate();
        return HouseResource::collection($houses);
    }

    /**
     * 新增
     * @param HouseRequest $request
     * @param House $house
     * @return HouseInfoResource
     */
    public function store(HouseRequest $request, House $house): HouseInfoResource
    {
        $user = $request->user();
        $house->fill($request->all());
        $house->user()->associate($user);
        $house->save();
        return new HouseInfoResource($house);
    }

    /**
     * 详情
     * @param House $house
     * @return HouseInfoResource
     */
    public function show(House $house): HouseInfoResource
    {
        return new HouseInfoResource($house);
    }
}
