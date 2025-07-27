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
use App\Models\HouseViewHistory;
use Illuminate\Database\Eloquent\Builder;
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
        $keyword = $request->input('keyword', '');
        $ids = $request->input('ids', '');
        $communityId = $request->input('community_id');
        $livingRoomCount = $request->input('living_room_count', -1);
        $minRentPrice = $request->input('min_rent_price', -1);
        $maxRentPrice = $request->input('max_rent_price', -1);
        $type = $request->input('type', -1);
        $minArea = $request->input('min_area', 0);
        $maxArea = $request->input('max_area', 0);
        $sort = $request->input('sort', '');
        $direction = $request->input('direction', '');
        $district = $request->input('district', '');
        $orientation = $request->input('orientation', '');
        $newType = $request->input('new_type', '');

        $builder = House::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->with([
                'community'
            ]);
        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($ids)) {
            $ids = explode(',', $ids);
            $builder = $builder->whereIn('id', $ids);
        }

        if (!empty($district)) {
            $district = explode(',', $district);
            $builder = $builder->whereIn('district', $district);
        }

        if (!empty($newType)) {
            switch ($newType) {
                case 'today':
                    $builder = $builder->whereDate('created_at', today());
                    break;
                case 'three_days':
                    $builder = $builder->whereBetween('created_at', [today()->subDays(3), today()]);
                    break;
                case 'week':
                    $builder = $builder->whereBetween('created_at', [today()->subDays(7), today()]);
                    break;
                case 'month':
                    $builder = $builder->whereBetween('created_at', [today()->subDays(30), today()]);
                    break;
            }
        }

        if ($communityId) {
            $builder = $builder->where('community_id', $communityId);
        }

        if ($livingRoomCount > 0) {
            $builder = $builder->where('living_room_count', $livingRoomCount);
        }

        if ($minRentPrice > 0 && $maxRentPrice > 0) {
            if ((int)$maxRentPrice === -1) {
                $builder = $builder->where('rent_price', '>', $minRentPrice);
            } else {
                $builder = $builder->whereBetween('rent_price', [$minRentPrice, $maxRentPrice]);
            }
        }

        if ($type > -1) {
            $builder = $builder->where('type', $type);
        }

        if ($minArea > 0 && $maxArea > 0) {
            if ((int)$minArea === -1) {
                $builder = $builder->where('area', '>', $minArea);
            } else {
                $builder = $builder->whereBetween('area', [$minArea, $maxArea]);
            }
        }

        if (!empty($orientation)) {
            $orientation = explode(',', $orientation);
            $builder = $builder->whereIn('orientation', $orientation);
        }

        if (!empty($sort) && !empty($direction)) {
            $builder = $builder->orderBy($sort, $direction);
        } else {
            $builder = $builder->latest();
        }

        $houses = $builder->paginate();
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

        if (!empty($data['video'])) {
            $video = json_decode($data['video'], true);
            $data['video'] = $video['path'];
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
        return new HouseInfoResource($house->load(['community:id,name']));
    }

    /**
     * 我的
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function myIndex(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $isDraft = $request->input('is_draft', 0);

        $houses = House::query()
            ->whereBelongsTo($user)
            ->where('is_draft', $isDraft)
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
