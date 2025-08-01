<?php
/**
 * 房源 Controller
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $facilityIds = $request->input('facilities_ids', '');
        $businessDistrictId = $request->input('business_district_id', '');


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

        if (!empty($businessDistrictId)) {
            $businessDistrictId = explode(',', $businessDistrictId);
            $builder = $builder->whereHas('community', function ($query) use ($businessDistrictId) {
                $query->where(function ($q) use ($businessDistrictId) {
                    foreach ($businessDistrictId as $id) {
                        $q->orWhereJsonContains('business_district_ids', $id);
                    }
                });
            });
        }

        if (!empty($district)) {
            $district = explode(',', $district);
            $builder = $builder->whereIn('district', $district);
        }

        if (!empty($facilityIds)) {
            $facilityIds = explode(',', $facilityIds);
            foreach ($facilityIds as $id) {
                $builder = $builder->whereJsonContains('facility_ids', $id);
            }
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
     * 草稿列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function draftIndex(Request $request): AnonymousResourceCollection
    {
        $builder = House::query()
            ->where('is_draft', 1)
            ->where('user_id', 0)
            ->with([
                'community'
            ]);
        $houses = $builder->paginate();
        return HouseResource::collection($houses);
    }

    /**
     * 新增
     * @param HouseRequest $request
     * @param House $house
     * @return HouseInfoResource
     * @throws InvalidRequestException
     */
    public function store(HouseRequest $request, House $house): HouseInfoResource
    {
        $user = $request->user();
        $data = $request->all();
        $isCreateDraft = $request->input('is_create_draft', 0);

//        $oldHouse = House::query()
//            ->where('community_id', $data['community_id'])
//            ->where('building_number', $data['building_number'])
//            ->where('room_number', $data['room_number'])
//            ->first();
//        if ($oldHouse) {
//            throw new InvalidRequestException('房源已存在,请重试！');
//        }

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
        if ($isCreateDraft && (int)$data['is_draft'] === 1) {
            $house->user_id = 0;
        } else {
            $house->user()->associate($user);
        }
        $house->save();
        return new HouseInfoResource($house);
    }

    /**
     * 详情
     * @param Request $request
     * @param House $house
     * @return HouseInfoResource
     */
    public function show(Request $request, House $house): HouseInfoResource
    {
        $user = $request->user();

        HouseViewHistory::query()->where('user_id', $user->id)->where('house_id', $house->id)->delete();
        $houseViewHistory = new HouseViewHistory();
        $houseViewHistory->user()->associate($user);
        $houseViewHistory->house()->associate($house);
        $houseViewHistory->save();

        return new HouseInfoResource($house->load(['community']));
    }

    /**
     * 编辑
     * @param HouseRequest $request
     * @param House $house
     * @return HouseInfoResource
     * @throws InvalidRequestException
     */
    public function update(HouseRequest $request, House $house): HouseInfoResource
    {
        $data = $request->all();
        $isDraft = $request->input('is_draft', 1);
//        $oldHouse = House::query()
//            ->whereNot('id', $house->id)
//            ->where('community_id', $data['community_id'])
//            ->where('building_number', $data['building_number'])
//            ->where('room_number', $data['room_number'])
//            ->first();
//        if ($oldHouse) {
//            throw new InvalidRequestException('房源已存在,请重试！');
//        }
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

        Log::info('--测试--');
        Log::info($isDraft);

        if ((int)$isDraft === 0) {
            $house->user()->associate($request->user());
        }

        $house->update();
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

    /**
     * 附件房源
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function nearby(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

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

        $facilityIds = $request->input('facilities_ids', '');

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


        if (!empty($district)) {
            $district = explode(',', $district);
            $builder = $builder->whereIn('district', $district);
        }


        if (!empty($facilityIds)) {
            $facilityIds = explode(',', $facilityIds);
            foreach ($facilityIds as $id) {
                $builder = $builder->whereJsonContains('facility_ids', $id);
            }
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


        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%');
            });
        }

        $houses = $builder->select('*')
            ->addSelect(DB::raw("acos(cos(" . $latitude . "*pi()/180)*cos(latitude*pi()/180)*cos(" . $longitude . "*pi()/180-longitude*pi()/180)+sin(" . $latitude . "*pi()/180)*sin(latitude * pi()/180)) * 6367000 AS distance"))
//            ->having('distance', '<=', 5000) // 添加距离限制（5000米 = 5公里）
            ->orderBy('distance')
            ->paginate();
        return HouseResource::collection($houses);
    }

    /**
     * 删除
     * @param House $house
     * @return Response
     */
    public function destroy(House $house): Response
    {
        $house->delete();
        return response()->noContent();
    }

    /**
     * 批量删除
     * @param Request $request
     * @return Response
     * @throws InvalidRequestException
     */
    public function batchDestroy(Request $request): Response
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            throw new InvalidRequestException('缺少必要参数!');
        }
        $ids = explode(',', $ids);
        House::query()->whereIn('id', $ids)->delete();
        return response()->noContent();
    }
}
