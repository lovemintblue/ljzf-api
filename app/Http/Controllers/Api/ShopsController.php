<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Http\Resources\Shop\ShopInfoResource;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopsController extends Controller
{
    public function index()
    {

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
        $shop->fill($request->all());
        $shop->user()->associate($user);
        $shop->save();
        return new ShopInfoResource($shop);
    }
}
