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
use Illuminate\Http\Response;

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
        $data = $request->all();

        if (!empty($data['images'])) {
            $images = json_decode($data['images'], true);
            $data['images'] = collect($images)->pluck('path')->toArray();
        } else {
            $data['images'] = [];
        }

        if (!empty($data['facility_ids'])) {
            $data['facility_ids'] = json_decode($data['facility_ids'], true);
        } else {
            $data['facility_ids'] = [];
        }

        $house->fill($data);
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

    /**
     * 我的
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function myIndex(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $houses = House::query()
            ->whereBelongsTo($user)
            ->latest()
            ->paginate();
        return HouseResource::collection($houses);
    }


    /**
     * 收藏
     * @param Request $request
     * @param House $house
     * @return Response
     */
    public function favor(Request $request, House $house): Response
    {
        $user = $request->user();
        if ($user->favoriteHouses()->find($house->id)) {
            return response()->noContent();
        }
        $user->favoriteHouses()->attach($house);
        return response()->noContent();
    }

    /**
     * 取消收藏
     * @param Request $request
     * @param House $house
     * @return Response
     */
    public function disfavor(Request $request, House $house): Response
    {
        $user = $request->user();
        $user->favoriteHouses()->detach($house);
        return response()->noContent();
    }

    /**
     * 收藏列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function favorites(Request $request): AnonymousResourceCollection
    {
        $houses = $request->user()->favoriteHouses()->paginate(16);
        return HouseResource::collection($houses);
    }
}
