<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserInfoResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $user->latest_visit_at = Carbon::now();
        $user->save();
        return new UserInfoResource($user->loadCount([
            'favoriteHouses',
            'favoriteShops',
            'houses' => function ($query) {
                $query->where('is_draft', 1);
            },
            'notifications',
            'userLevel'
        ]));
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

    /**
     * 减少查看电话次数
     * @param Request $request
     * @return Response
     * @throws InvalidRequestException
     */
    public function decrementViewPhoneCount(Request $request): Response
    {
        $user = $request->user();
        if ((int)$user->view_phone_count === 0) {
            throw new InvalidRequestException('查看次数不足！');
        }
        $user->decrement('view_phone_count');
        return response()->noContent();
    }
}
