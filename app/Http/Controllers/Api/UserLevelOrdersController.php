<?php
/**
 * 用户等级订单 Controller
 */

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLevelOrderRequest;
use App\Http\Resources\UserLevelOrder\UserLevelOrderInfoResource;
use App\Models\UserLevel;
use App\Models\UserLevelOrder;
use Illuminate\Http\Request;

class UserLevelOrdersController extends Controller
{
    /**
     * 新增
     * @param UserLevelOrderRequest $request
     * @param UserLevelOrder $userLevelOrder
     * @return UserLevelOrderInfoResource
     * @throws InvalidRequestException
     */
    public function store(UserLevelOrderRequest $request, UserLevelOrder $userLevelOrder): UserLevelOrderInfoResource
    {
        $userLevelId = $request->input('user_level_id');

        $userLevel = UserLevel::query()->where('id', $userLevelId)->first();

        if (!$userLevel) {
            throw new InvalidRequestException('会员等级不存在，请重试！');
        }

        $userLevelOrder->total_amount = $userLevelOrder->price;
        $userLevelOrder->user()->associate($request->user());
        $userLevelOrder->userLevel()->associate($userLevel);
        $userLevelOrder->save();

        return new UserLevelOrderInfoResource($userLevelOrder);
    }
}
