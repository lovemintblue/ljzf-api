<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserInfoResource;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * 登录信息
     * @param Request $request
     * @return UserInfoResource
     */
    public function me(Request $request): UserInfoResource
    {
        $user = $request->user();
        return new UserInfoResource($user->loadCount(['favoriteHouses', 'favoriteShops', 'houses', 'notifications']));
    }

    /**
     * 编辑
     * @param Request $request
     * @return UserInfoResource
     */
    public function update(Request $request): UserInfoResource
    {
        $user = $request->user();
        $user->fill($request->input());
        $user->update();
        return new UserInfoResource($user);
    }
}
