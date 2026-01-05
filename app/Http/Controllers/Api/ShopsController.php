<?php
/**
 * 商铺 Controller
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Http\Resources\Shop\ShopInfoResource;
use App\Http\Resources\Shop\ShopResource;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ShopsController extends Controller
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
        $businessDistrictId = $request->input('business_district_id', 0);
        $district = $request->input('district', '');
        $minRentPrice = (int)$request->input('min_rent_price', 0);
        $maxRentPrice = (int)$request->input('max_rent_price', 0);
        $type = $request->input('type', '');
        $minArea = (int)$request->input('min_area', 0);
        $maxArea = (int)$request->input('max_area', 0);
        $sort = $request->input('sort', '');
        $direction = $request->input('direction', '');
        $newType = $request->input('new_type', '');

        $builder = Shop::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->with('community');

        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (!empty($ids)) {
            $ids = explode(',', $ids);
            $builder = $builder->whereIn('id', $ids);
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

        if (!empty($businessDistrictId)) {
            $businessDistrictId = explode(',', $businessDistrictId);
            $builder = $builder->whereHas('community', function (Builder $query) use ($businessDistrictId) {
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

        if ($type !== '' && $type !== null) {
            $type = explode(',', $type);
            $builder = $builder->whereIn('type', $type);
        }

        if ($minRentPrice >= 0 && $maxRentPrice > 0) {
            if ((int)$maxRentPrice === -1) {
                $builder = $builder->where('rent_price', '>', $minRentPrice);
            } else {
                $builder = $builder->whereBetween('rent_price', [$minRentPrice, $maxRentPrice]);
            }
        }

        if ($minArea >= 0 && $maxArea > 0) {
            $builder = $builder->whereBetween('area', [$minArea, $maxArea]);
        }

        if (!empty($sort) && !empty($direction)) {
            $builder = $builder->orderBy($sort, $direction);
        } else {
            $builder = $builder->latest();
        }

        $shops = $builder->paginate();
        return ShopResource::collection($shops);
    }

    /**
     * 新增
     * @param ShopRequest $request
     * @param Shop $shop
     * @return ShopInfoResource
     */
    public function store(ShopRequest $request, Shop $shop): ShopInfoResource
    {
        $user = $request->user();
        $data = $request->all();
        
        // 移除已废弃的字段
        unset($data['business_district_id']);

        if (!empty($data['images'])) {
            $images = json_decode($data['images'], true);
            $data['images'] = collect($images)->pluck('path')->toArray();
        } else {
            $data['images'] = [];
        }

        if (!empty($data['video'])) {
            $video = json_decode($data['video'], true);
            $data['video'] = $video[0]['path'];
        }

        if (!empty($data['facility_ids'])) {
            $data['facility_ids'] = json_decode($data['facility_ids'], true);
        } else {
            $data['facility_ids'] = [];
        }

        if (!empty($data['industry_ids'])) {
            $data['industry_ids'] = json_decode($data['industry_ids'], true);
        } else {
            $data['industry_ids'] = [];
        }

        if (!empty($data['suitable_businesses'])) {
            $data['suitable_businesses'] = json_decode($data['suitable_businesses'], true);
        } else {
            $data['suitable_businesses'] = [];
        }

        $shop->fill($data);
        $shop->user()->associate($user);
        $shop->save();
        
        // 发送待审核通知（如果商铺有审核状态且为待审核）
        if (isset($shop->audit_status) && $shop->audit_status == 0) {
            (new NotificationService())->notifyShopPendingAudit($user, $shop);
        }
        
        return new ShopInfoResource($shop);
    }

    /**
     * 详情
     * @param Shop $shop
     * @return ShopInfoResource
     */
    public function show(Shop $shop): ShopInfoResource
    {
        return new ShopInfoResource($shop->load('community'));
    }

    /**
     * 收藏
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function favor(Request $request, Shop $shop): Response
    {
        $user = $request->user();
        if ($user->favoriteShops()->find($shop->id)) {
            return response()->noContent();
        }
        $user->favoriteShops()->attach($shop);
        return response()->noContent();
    }

    /**
     * 取消收藏
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function disfavor(Request $request, Shop $shop): Response
    {
        $user = $request->user();
        $user->favoriteShops()->detach($shop);
        return response()->noContent();
    }

    /**
     * 收藏列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function favorites(Request $request): AnonymousResourceCollection
    {
        $houses = $request->user()->favoriteShops()->paginate();
        return ShopResource::collection($houses);
    }

    /**
     * 编辑商铺
     * @param ShopRequest $request
     * @param Shop $shop
     * @return ShopInfoResource
     */
    public function update(ShopRequest $request, Shop $shop): ShopInfoResource
    {
        $data = $request->all();
        
        // 移除已废弃的字段
        unset($data['business_district_id']);

        if (!empty($data['images'])) {
            $images = json_decode($data['images'], true);
            $data['images'] = collect($images)->pluck('path')->toArray();
        } else {
            $data['images'] = [];
        }

        if (!empty($data['video'])) {
            $video = json_decode($data['video'], true);
            $data['video'] = $video[0]['path'];
        }

        if (!empty($data['facility_ids'])) {
            $data['facility_ids'] = json_decode($data['facility_ids'], true);
        } else {
            $data['facility_ids'] = [];
        }

        if (!empty($data['industry_ids'])) {
            $data['industry_ids'] = json_decode($data['industry_ids'], true);
        } else {
            $data['industry_ids'] = [];
        }

        if (!empty($data['suitable_businesses'])) {
            $data['suitable_businesses'] = json_decode($data['suitable_businesses'], true);
        } else {
            $data['suitable_businesses'] = [];
        }

        $shop->fill($data);
        $shop->update();
        
        return new ShopInfoResource($shop);
    }

    /**
     * 我的商铺
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function myIndex(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $auditStatus = $request->input('audit_status');
        $keyword = $request->input('keyword', '');
        
        $builder = Shop::query()->whereBelongsTo($user)->with(['community'])->latest();
        
        // 关键词搜索：房号、小区名、商铺编号
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
        
        // 如果传了审核状态参数，则筛选
        if (isset($auditStatus)) {
            $builder = $builder->where('audit_status', $auditStatus);
        }
        
        $shops = $builder->paginate();
        return ShopResource::collection($shops);
    }

    /**
     * 上下架
     * @param Request $request
     * @return Response
     * @throws InvalidRequestException
     */
    public function changeShow(Request $request): Response
    {
        $shop_id = $request->input('shop_id');
        $is_show = $request->input('is_show', 0);
        
        if (empty($shop_id)) {
            throw new InvalidRequestException('缺少必要参数!');
        }
        
        $shop = Shop::query()->where('id', $shop_id)->first();
        if (!$shop) {
            throw new InvalidRequestException('商铺不存在!');
        }
        
        $wasShown = $shop->is_show;
        $shop->is_show = $is_show;
        $shop->save();
        
        // 发送下架通知
        if ($wasShown && !$is_show && $shop->user) {
            $reason = $request->input('reason', '用户主动下架');
            (new NotificationService())->notifyShopOffline($shop->user, $shop, $reason);
        }
        
        return response()->noContent();
    }

    /**
     * 附近商铺（地图）
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function nearby(Request $request): AnonymousResourceCollection
    {
        $keyword = $request->input('keyword');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        
        $businessDistrictId = $request->input('business_district_id', 0);
        $district = $request->input('district', '');
        $minRentPrice = (int)$request->input('min_rent_price', 0);
        $maxRentPrice = (int)$request->input('max_rent_price', 0);
        $type = $request->input('type', '');
        $minArea = (int)$request->input('min_area', 0);
        $maxArea = (int)$request->input('max_area', 0);
        $newType = $request->input('new_type', '');

        $builder = Shop::query()
            ->where('is_show', 1)
            ->where('audit_status', 1)
            ->with(['community']);
            
        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
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

        if ($minRentPrice >= 0 && $maxRentPrice > 0) {
            if ((int)$maxRentPrice === -1) {
                $builder = $builder->where('rent_price', '>', $minRentPrice);
            } else {
                $builder = $builder->whereBetween('rent_price', [$minRentPrice, $maxRentPrice]);
            }
        }

        if ($type !== '' && $type !== null) {
            $type = explode(',', $type);
            $builder = $builder->whereIn('type', $type);
        }

        if ($minArea >= 0 && $maxArea > 0) {
            $builder = $builder->whereBetween('area', [$minArea, $maxArea]);
        }

        if (!empty($keyword)) {
            $builder = $builder->where(function (Builder $query) use ($keyword) {
                return $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%')
                    ->orWhereHas('community', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        // 根据经纬度计算距离并排序
        $shops = $builder->select('*')
            ->addSelect(\DB::raw("acos(cos(" . $latitude . "*pi()/180)*cos(latitude*pi()/180)*cos(" . $longitude . "*pi()/180-longitude*pi()/180)+sin(" . $latitude . "*pi()/180)*sin(latitude * pi()/180)) * 6367000 AS distance"))
            ->orderBy('distance')
            ->paginate();
            
        return ShopResource::collection($shops);
    }
}
