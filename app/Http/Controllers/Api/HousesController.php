<?php
/**
 * 房源 Controller
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\HouseRequest;
use App\Http\Resources\House\FavoriteHouseResource;
use App\Http\Resources\House\HouseEditInfoResource;
use App\Http\Resources\House\HouseInfoResource;
use App\Http\Resources\House\HouseResource;
use App\Models\BusinessDistrict;
use App\Models\Community;
use App\Models\House;
use App\Models\HouseOperationLog;
use App\Models\HouseViewHistory;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HousesController extends Controller
{
    /**
     * 处理房屋类型关键词映射，并提取纯净的搜索关键词
     * 将用户输入的口语化关键词转换为数据库查询条件
     * 
     * @param string $keyword
     * @return array ['room_count' => int|null, 'living_room_count' => int|null, 'is_single' => bool, 'clean_keyword' => string]
     */
    private function parseRoomTypeKeyword(string $keyword): array
    {
        $result = [
            'room_count' => null,
            'living_room_count' => null,
            'is_single' => false,
            'clean_keyword' => $keyword // 默认返回原始关键词
        ];
        
        $cleanKeyword = $keyword;
        
        // 单间关键词 - 支持独立关键词和混合关键词（如"鸿泰单间"）
        if (preg_match('/(单间|一间)/u', $keyword)) {
            $result['is_single'] = true;
            // 从关键词中移除"单间"或"一间"
            $cleanKeyword = preg_replace('/(单间|一间)/u', '', $cleanKeyword);
        }
        
        // 中文到数字的映射
        $chineseToNumber = [
            '零' => 0,
            '一' => 1,
            '二' => 2,
            '两' => 2,
            '三' => 3,
            '四' => 4,
            '五' => 5,
        ];
        
        // 室数关键词匹配（支持：一房、1房、一室、1室、两房、2居、两居等）
        if (preg_match('/(两|[一二三四五1-5])(房|室|居)/u', $keyword, $matches)) {
            $num = $matches[1];
            // 转换为数字
            if (is_numeric($num)) {
                $result['room_count'] = (int)$num;
            } else {
                $result['room_count'] = $chineseToNumber[$num] ?? null;
            }
            // 从关键词中移除室数部分
            $cleanKeyword = preg_replace('/(两|[一二三四五1-5])(房|室|居)/u', '', $cleanKeyword);
        }
        
        // 厅数关键词匹配（支持：0厅、零厅、一厅、1厅、两厅等）
        if (preg_match('/(零|两|[一二三0-3])厅/u', $keyword, $matches)) {
            $num = $matches[1];
            // 转换为数字
            if (is_numeric($num)) {
                $result['living_room_count'] = (int)$num;
            } else {
                $result['living_room_count'] = $chineseToNumber[$num] ?? null;
            }
            // 从关键词中移除厅数部分
            $cleanKeyword = preg_replace('/(零|两|[一二三0-3])厅/u', '', $cleanKeyword);
        }
        
        // 清理多余的空格
        $result['clean_keyword'] = trim($cleanKeyword);
        
        return $result;
    }

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
        $facilityIds = $request->input('facilities_ids', -1);
        $businessDistrictId = $request->input('business_district_id', '');
        $roomCount = $request->input('room_count', -1);
        $isDelegated = $request->input('is_delegated');
        $minFloor = $request->input('min_floor', -1);
        $maxFloor = $request->input('max_floor', -1);


        $builder = House::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->with([
                'community'
            ]);
        if (!empty($keyword)) {
            // 解析房屋类型关键词，并提取纯净的搜索词
            $roomTypeInfo = $this->parseRoomTypeKeyword($keyword);
            $cleanKeyword = $roomTypeInfo['clean_keyword'];
            
            // 如果有纯净的关键词（小区名或商圈名），进行搜索
            if (!empty($cleanKeyword)) {
                // 先通过商圈名查询商圈ID
                $businessDistrictIds = BusinessDistrict::where('name', 'like', '%' . $cleanKeyword . '%')
                    ->pluck('id')
                    ->toArray();
                
                $builder = $builder->where(function (Builder $query) use ($cleanKeyword, $businessDistrictIds) {
                    $query->where('title', 'like', '%' . $cleanKeyword . '%')
                        ->orWhere('no', '=', $cleanKeyword)
                        // 搜索小区名
                        ->orWhereHas('community', function ($q) use ($cleanKeyword, $businessDistrictIds) {
                            $q->where('name', 'like', '%' . $cleanKeyword . '%');
                            
                            // 如果商圈名匹配，搜索包含该商圈的小区
                            if (!empty($businessDistrictIds)) {
                                $q->orWhere(function ($bq) use ($businessDistrictIds) {
                                    foreach ($businessDistrictIds as $id) {
                                        // Convert ID to string because JSON stores IDs as strings
                                        $bq->orWhereJsonContains('business_district_ids', (string)$id);
                                    }
                                });
                            }
                        });
                });
            }
            
            // 如果识别到户型信息，用 AND 条件筛选（而不是 OR）
            // 如果是单间，匹配 room_count = 1 且 living_room_count = 0
            if ($roomTypeInfo['is_single']) {
                $builder = $builder->where('room_count', 1)
                    ->where('living_room_count', 0);
            }
            // 如果同时识别到室数和厅数，精确匹配两者
            elseif ($roomTypeInfo['room_count'] !== null && $roomTypeInfo['living_room_count'] !== null) {
                $builder = $builder->where('room_count', $roomTypeInfo['room_count'])
                    ->where('living_room_count', $roomTypeInfo['living_room_count']);
            }
            // 如果只识别到室数（没有厅数），只匹配室数
            elseif ($roomTypeInfo['room_count'] !== null) {
                $builder = $builder->where('room_count', $roomTypeInfo['room_count']);
            }
        }

        if ((int)$roomCount !== -1) {
            $roomCount = explode(',', $roomCount);
            $builder = $builder->whereIn('room_count', $roomCount);
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

        if (empty($facilityIds)) {
            $facilityIds = -1;
        }

        if ((int)$facilityIds !== -1) {
            Log::info('进入筛选');
            $facilityIds = explode(',', $facilityIds);
            $builder = $builder->where(function ($q) use ($facilityIds) {
                foreach ($facilityIds as $id) {
                    $q->orWhereJsonContains('facility_ids', (int)$id);
                }
            });
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

        if ($livingRoomCount >= 0) {
            $builder = $builder->where('living_room_count', $livingRoomCount);
        }

        if ($minRentPrice >= 0 && $maxRentPrice > 0) {
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

        if ($minFloor > 0 || $maxFloor > 0) {
            if ($minFloor > 0 && $maxFloor > 0) {
                $builder = $builder->whereBetween('floor', [$minFloor, $maxFloor]);
            } elseif ($minFloor > 0) {
                $builder = $builder->where('floor', '>=', $minFloor);
            } elseif ($maxFloor > 0) {
                $builder = $builder->where('floor', '<=', $maxFloor);
            }
        }

        if (isset($isDelegated)) {
            $builder = $builder->where('is_delegated', 1);
        }

        // 置顶排序：先按是否置顶降序，再按置顶时间降序，最后按用户指定排序或创建时间
        if (!empty($sort) && !empty($direction)) {
            $builder = $builder->orderBy('is_top', 'desc')
                ->orderBy('top_at', 'desc')
                ->orderBy($sort, $direction);
        } else {
            $builder = $builder->orderBy('is_top', 'desc')
                ->orderBy('top_at', 'desc')
                ->latest();
        }

        $houses = $builder->paginate();
        return HouseResource::collection($houses);
    }

    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function delegated(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword', '');

        $builder = House::query()
            ->where('is_show', 1)
            ->where('audit_status', 0)
            ->where('is_draft', 0)
            ->where('is_delegated', 1)
            ->with([
                'community'
            ]);
        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
//                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhere('no', '=', $keyword)
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
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
        $keyword = $request->input('keyword', '');

        $builder = House::query()
            ->where('is_draft', 1)
            ->where('user_id', 0)
            ->with([
                'community'
            ]);

        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
//                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
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
        $isCreateDraft = $request->input('is_create_draft', 0);
        Log::info('--打印提交的参数--');
        Log::info($data);
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

        if (!empty($data['id'])){
            //查重
//            $house_exit = (new House())->where('community_id',$data['community_id'])
//                ->where('id','<>',$data['id'])
//                ->where('building_number',$data['building_number'])
//                ->where('unit',$data['unit'])
//                ->where('floor',$data['floor'])
//                ->where('room_number',$data['room_number'])
//                ->first();
//            if ($house_exit) {
//                throw new InvalidRequestException('检测到房源已上架，重复房源编号为'.$house_exit->no);
//            }
            //修改
            $data['audit_status'] = 1;
            $house = (new House())->where('id',$data['id'])->first();
            $house->fill($data);
            $house->save();
            return new HouseInfoResource($house);
        }

        // 已取消发布接口的防重复检测，改为使用独立的实时检测接口

        $house->fill($data);
        if ($isCreateDraft && (int)$data['is_draft'] === 1) {
            Log::info('断点11111');
            Log::info($isCreateDraft);
            Log::info($data['is_draft']);
            $house->user_id = 0;
        } else {
            Log::info('断点22222');
            $house->user()->associate($user);
        }

        // 如果用户是员工 - 不需要审核
        if ($user->is_staff) {
            $house->audit_status = 1;
        }
        
        // 处理定时发布逻辑
        if (!empty($data['scheduled_publish_at'])) {
            // 如果设置了定时发布，将房源设置为未上架状态
            $house->is_show = 0;
            $house->scheduled_publish_at = $data['scheduled_publish_at'];
        }

        $house->save();
        
        // 员工发布房源自动审核通过，记录首次发布日志
        if ($user->is_staff && $house->audit_status == 1 && !$isCreateDraft) {
            HouseOperationLog::create([
                'house_id' => $house->id,
                'operator_id' => $user->id,
                'operator_type' => 'user',
                'operation_type' => 'publish',
            ]);
        }
        
        // 发送通知
        if (!$user->is_staff && $house->audit_status == 0) {
            // 普通用户发布房源，发送待审核通知
            (new NotificationService())->notifyHousePendingAudit($user, $house);
        }
        
        return new HouseInfoResource($house);
    }

    /**
     * @param Request $request
     * @param House $house
     * @return HouseEditInfoResource|HouseInfoResource
     */
    public function show(Request $request, House $house)
    {
        $isEdit = $request->input('is_edit', 0);
        $user_id = $request->input('user_id', '');
        if (!empty($user_id)){
            HouseViewHistory::query()->where('user_id', $user_id)->where('house_id', $house->id)->delete();
            $houseViewHistory = new HouseViewHistory();
            $houseViewHistory->user_id = $user_id;
            $houseViewHistory->house()->associate($house);
            $houseViewHistory->save();
        }

        if ($isEdit) {
            return new HouseEditInfoResource($house->load(['community']));
        }
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
        $isDelegated = $request->input('is_delegated', 0);

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
        
        // 检测 is_show 字段的变化，用于记录操作日志
        $wasShown = (int)$house->is_show;
        $newIsShow = isset($data['is_show']) ? (int)$data['is_show'] : $wasShown;
        
        $house->fill($data);

        if ((int)$isDraft === 0 && (int)$isDelegated === 0) {
            $house->user()->associate($request->user());
        }
        
        // 处理定时发布逻辑
        if (!empty($data['scheduled_publish_at'])) {
            // 如果设置了定时发布，将房源设置为未上架状态
            $house->is_show = 0;
            $house->scheduled_publish_at = $data['scheduled_publish_at'];
            $newIsShow = 0; // 更新状态用于日志记录
        }

        $house->last_updated_at = now(); // 更新最后更新时间
        $house->update();
        
        // 如果 is_show 状态发生变化，记录操作日志
        if ($wasShown !== $newIsShow) {
            $user = $request->user();
            
            if ($wasShown === 1 && $newIsShow === 0) {
                // 下架操作：从上架变为下架
                HouseOperationLog::create([
                    'house_id' => $house->id,
                    'operator_id' => $user ? $user->id : null,
                    'operator_type' => 'user',
                    'operation_type' => 'offline',
                    'reason' => $request->input('reason', '用户主动下架'),
                ]);
            } elseif ($wasShown === 0 && $newIsShow === 1) {
                // 上架操作：从下架变为上架
                HouseOperationLog::create([
                    'house_id' => $house->id,
                    'operator_id' => $user ? $user->id : null,
                    'operator_type' => 'user',
                    'operation_type' => 'online',
                ]);
            }
        }
        
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
        $isDelegated = $request->input('is_delegated');
        $keyword = $request->input('keyword', '');

        Log::info('--打印草稿参数--:' . $isDraft);
        Log::info('--打印委托参数--:' . $isDelegated);
        Log::info('--打印搜索关键词--:' . $keyword);

        $builder = House::query()->whereBelongsTo($user)->with(['community'])->latest();

        // 关键词搜索：房号、小区名、房源编号
        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('room_number', 'like', '%' . $keyword . '%')
                    ->orWhere('no', 'like', '%' . $keyword . '%')
                    ->orWhere('building_number', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (isset($isDelegated) && (int)$isDelegated === 1) {
            Log::info('委托');
            $builder = $builder->where('is_delegated', $isDelegated);
        }

        if (isset($isDraft)) {
            Log::info('草稿');
            $builder = $builder->where('is_draft', $isDraft);
        }

        $houses = $builder->paginate();
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
        $houses = $request->user()->favoriteHouses()->paginate();

        return FavoriteHouseResource::collection($houses);
    }

    /**
     * 附近房源
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
//                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
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


        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
//                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
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

    /**
     * 锁定
     * @param Request $request
     * @param House $house
     * @return Response
     */
    public function lock(Request $request, House $house): Response
    {
        $user = $request->user();
        $house->is_locked = 1;
        $house->lock_user_id = $user->id;
        $house->save();
        return response()->noContent();
    }

    /**
     * 取消锁定
     * @param House $house
     * @return Response
     */
    public function unlock(House $house): Response
    {
        $house->is_locked = 0;
        $house->lock_user_id = 0;
        $house->save();
        return response()->noContent();
    }

    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function all(Request $request): AnonymousResourceCollection
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
        $facilityIds = $request->input('facilities_ids', -1);
        $businessDistrictId = $request->input('business_district_id', '');
        $roomCount = $request->input('room_count', -1);
        $isDelegated = $request->input('is_delegated');


        $builder = House::query()
            ->where('audit_status', 1)
            ->where('is_draft', 0)
            ->with([
                'community'
            ]);
        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
//                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhere('no', '=', $keyword)
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ((int)$roomCount !== -1) {
            $roomCount = explode(',', $roomCount);
            $builder = $builder->whereIn('room_count', $roomCount);
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

        if (empty($facilityIds)) {
            $facilityIds = -1;
        }

        if ((int)$facilityIds !== -1) {
            Log::info('进入筛选');
            $facilityIds = explode(',', $facilityIds);
            $builder = $builder->where(function ($q) use ($facilityIds) {
                foreach ($facilityIds as $id) {
                    $q->orWhereJsonContains('facility_ids', (int)$id);
                }
            });
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

        if ($livingRoomCount >= 0) {
            $builder = $builder->where('living_room_count', $livingRoomCount);
        }

        if ($minRentPrice >= 0 && $maxRentPrice > 0) {
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

        if (isset($isDelegated)) {
            $builder = $builder->where('is_delegated', 1);
        }

        if (!empty($sort) && !empty($direction)) {
            $builder = $builder->orderBy($sort, $direction);
        } else {
            $builder = $builder->latest();
        }

        $houses = $builder->paginate();
        return HouseResource::collection($houses);
    }

    public function changeShow(Request $request)
    {
        $house_id = $request->input('house_id');
        $is_show = $request->input('is_show',0);
        if (empty($house_id)){
            throw new InvalidRequestException('缺少必要参数!');
        }
        $house = House::query()->where('id',$house_id)->first();
        if (!$house){
            throw new InvalidRequestException('房源不存在!');
        }
        
        // 确保类型一致，转换为整数进行比较
        $wasShown = (int)$house->is_show;
        $newIsShow = (int)$is_show;
        
        // 只有状态真正改变时才记录日志
        if ($wasShown !== $newIsShow) {
            $house->is_show = $newIsShow;
            $house->last_updated_at = now(); // 更新最后更新时间，以便待更新房源列表能正确刷新
            $house->save();
            
            $user = $request->user();
            
            // 记录操作日志
            if ($wasShown === 1 && $newIsShow === 0) {
                // 下架操作：从上架变为下架
                $reason = $request->input('reason', '用户主动下架');
                HouseOperationLog::create([
                    'house_id' => $house->id,
                    'operator_id' => $user ? $user->id : null,
                    'operator_type' => 'user',
                    'operation_type' => 'offline',
                    'reason' => $reason,
                ]);
                
                // 发送下架通知
                if ($house->user) {
                    (new NotificationService())->notifyHouseOffline($house->user, $house, $reason);
                }
            } elseif ($wasShown === 0 && $newIsShow === 1) {
                // 上架操作：从下架变为上架
                HouseOperationLog::create([
                    'house_id' => $house->id,
                    'operator_id' => $user ? $user->id : null,
                    'operator_type' => 'user',
                    'operation_type' => 'online',
                ]);
            }
        } else {
            // 状态没有改变，只更新 last_updated_at
            $house->last_updated_at = now();
            $house->save();
        }
        
        return response()->noContent();
    }

    /**
     * 检测房源是否重复（实时检测接口）
     * 用于员工发布房源时的实时检测
     * 防重复条件：小区 + 栋数 + 房号
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDuplicate(Request $request)
    {
        $communityId = $request->input('community_id');
        $buildingNumber = $request->input('building_number');
        $roomNumber = $request->input('room_number');
        $excludeId = $request->input('exclude_id'); // 编辑时排除当前房源ID
        
        // 验证必填参数
        if (empty($communityId) || empty($buildingNumber) || empty($roomNumber)) {
            return response()->json([
                'exists' => false,
                'message' => '请填写完整的小区、栋数和房号信息'
            ]);
        }
        
        // 查询是否存在重复房源（基于：小区 + 栋数 + 房号）
        $query = House::query()
            ->where('community_id', $communityId)
            ->where('building_number', $buildingNumber)
            ->where('room_number', $roomNumber);
        
        // 编辑时排除当前房源
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $duplicateHouse = $query->first();
        
        // 不存在重复
        if (!$duplicateHouse) {
            return response()->json([
                'exists' => false,
                'message' => '未检测到重复房源'
            ]);
        }
        
        // 存在重复 - 判断房源状态
        if ($duplicateHouse->is_show == 1) {
            // 已上架
            return response()->json([
                'exists' => true,
                'status' => 'online',
                'house_no' => $duplicateHouse->no,
                'house_id' => $duplicateHouse->id,
                'message' => '检测到房源已上架，重复房源编号为 ' . $duplicateHouse->no
            ], 400);
        } else {
            // 已下架
            return response()->json([
                'exists' => true,
                'status' => 'offline',
                'house_no' => $duplicateHouse->no,
                'house_id' => $duplicateHouse->id,
                'message' => '该房源已下架，房源库中编号为 ' . $duplicateHouse->no . '，如需继续上架请前往后台点击上架'
            ], 400);
        }
    }
}
