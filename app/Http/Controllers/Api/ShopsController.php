<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Http\Resources\Shop\ShopInfoResource;
use App\Http\Resources\Shop\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopsController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $shops = Shop::query()
            ->latest()
            ->paginate();
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
            $data['images'] = json_decode($data['images'], true);
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
}
