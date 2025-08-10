<?php
/**
 * 用户等级 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserLevel\UserLevelInfoResource;
use App\Http\Resources\UserLevel\UserLevelResource;
use App\Models\UserLevel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserLevelsController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userLevels = UserLevel::query()->get();
        UserLevelResource::wrap('data');
        return UserLevelResource::collection($userLevels);
    }

    /**
     * 详情
     * @param UserLevel $userLevel
     * @return UserLevelInfoResource
     */
    public function show(UserLevel $userLevel): UserLevelInfoResource
    {
        return new UserLevelInfoResource($userLevel->load('userLevelPrices'));
    }
}
