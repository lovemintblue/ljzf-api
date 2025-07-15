<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Http\Resources\Shop\ShopInfoResource;
use App\Http\Resources\Shop\ShopResource;
use App\Models\House;
use App\Models\Shop;
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
        $district = $request->input('district', '');
        $minRentPrice = $request->input('min_rent_price', -1);
        $maxRentPrice = $request->input('max_rent_price', -1);
        $type = $request->input('type', '');
        $minArea = $request->input('min_area', -1);
        $maxArea = $request->input('max_area', -1);
        $sort = $request->input('sort', '');
        $direction = $request->input('direction', '');

        $builder = Shop::query()
            ->with([
                'businessDistrict:id,name'
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

        if (!empty($type)) {
            $type = explode(',', $type);
            $builder = $builder->whereIn('type', $type);
        }

        if ($minRentPrice >= 0 && $maxRentPrice >= 0) {
            if ((int)$maxRentPrice === -1) {
                $builder = $builder->where('rent_price', '>', $minRentPrice);
            } else {
                $builder = $builder->whereBetween('rent_price', [$minRentPrice, $maxRentPrice]);
            }
        }

        if ($minArea >= 0 && $maxArea >= 0) {
            if ((int)$minArea === -1) {
                $builder = $builder->where('area', '>', $minArea);
            } else {
                $builder = $builder->whereBetween('area', [$minArea, $maxArea]);
            }
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

        if (!empty($data['industry_ids'])) {
            $data['industry_ids'] = json_decode($data['industry_ids'], true);
        } else {
            $data['industry_ids'] = [];
        }

        $shop->fill($data);
        $shop->user()->associate($user);
        $shop->save();
        return new ShopInfoResource($shop);
    }

    /**
     * 详情
     * @param Shop $shop
     * @return ShopInfoResource
     */
    public function show(Shop $shop): ShopInfoResource
    {
        return new ShopInfoResource($shop);
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
}
