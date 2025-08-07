<?php
/**
 * 用户分享房源 - Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserShareHouseRequest;
use App\Http\Resources\Api\UserShareHouse\UserShareHouseInfoResource;
use App\Models\House;
use App\Models\UserShareHouse;
use Illuminate\Http\Request;

class UserShareHousesController extends Controller
{
    /**
     * 新增
     * @param UserShareHouseRequest $request
     * @param UserShareHouse $userShareHouse
     * @return UserShareHouseInfoResource
     */
    public function store(UserShareHouseRequest $request, UserShareHouse $userShareHouse): UserShareHouseInfoResource
    {
        $user = $request->user();
        $userShareHouse->fill($request->validated());
        $userShareHouse->user()->associate($user);
        $userShareHouse->save();
        return new UserShareHouseInfoResource($userShareHouse);
    }

    /**
     * 详情
     * @param UserShareHouse $userShareHouse
     * @return UserShareHouseInfoResource
     */
    public function show(UserShareHouse $userShareHouse): UserShareHouseInfoResource
    {
        return new UserShareHouseInfoResource($userShareHouse);
    }
}
