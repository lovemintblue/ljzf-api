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
use App\Models\UserLevelPrice;
use Illuminate\Http\Request;
use Random\RandomException;
use Carbon\Carbon;

class UserLevelOrdersController extends Controller
{
    /**
     * 列表
     */
    public function list(Request $request)
    {
        $user = $request->user();
        $data = UserLevelOrder::query()->with('userLevel')->where('status',1)->where('user_id',$user->id)->orderBy('created_at','DESC')->paginate();
        // 格式化时间
        // 转换为数组后格式化时间
        $formattedData = $data->toArray();
        foreach ($formattedData['data'] as &$item) {
            $item['created_at'] = Carbon::parse($item['created_at'])->format('Y-m-d H:i:s');
            $item['updated_at'] = Carbon::parse($item['updated_at'])->format('Y-m-d H:i:s');
        }

        return response()->json($formattedData);
    }




    /**
     * 计算升级/续费价格
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidRequestException
     */
    public function calculatePrice(Request $request)
    {
        $user = $request->user();
        $targetPriceId = $request->input('user_level_price_id');
        
        $targetPrice = UserLevelPrice::with('userLevel')->find($targetPriceId);
        if (!$targetPrice) {
            throw new InvalidRequestException('目标会员套餐不存在！');
        }
        
        // 获取周期对应的天数
        $cycleDays = [
            0 => 30,   // 月度
            1 => 90,   // 季度
            2 => 365   // 年度
        ];
        
        $result = [
            'target_level_id' => $targetPrice->user_level_id,
            'target_level_name' => $targetPrice->userLevel->name,
            'target_cycle' => $targetPrice->cycle,
            'target_total_price' => $targetPrice->price,
            'original_price' => $targetPrice->price,
            'final_price' => $targetPrice->price,
            'discount_amount' => 0,
            'action_type' => 'open', // open:开通, renew:续期, upgrade:升级
            'current_level_id' => $user->user_level_id,
            'current_expired_at' => $user->expired_at,
        ];
        
        // 如果用户没有会员或会员已过期
        if (!$user->user_level_id || !$user->expired_at || Carbon::parse($user->expired_at)->isPast()) {
            $result['action_type'] = 'open';
            return response()->json($result);
        }
        
        // 用户有会员且未过期，计算剩余价值
        $currentLevel = $user->user_level_id;
        $targetLevel = $targetPrice->user_level_id;
        $expiredAt = Carbon::parse($user->expired_at);
        $now = Carbon::now();
        $remainingDays = $now->diffInDays($expiredAt, false);
        
        if ($remainingDays < 0) {
            $remainingDays = 0;
        }
        
        // 获取用户当前会员的价格信息（需要从最近的订单中获取）
        $lastOrder = UserLevelOrder::where('user_id', $user->id)
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastOrder) {
            $currentCycle = $lastOrder->cycle;
            $currentTotalPrice = $lastOrder->total_amount;
            $currentCycleDays = $cycleDays[$currentCycle] ?? 30;
            
            // 计算每日价值
            $dailyValue = $currentTotalPrice / $currentCycleDays;
            
            // 计算剩余价值
            $remainingValue = $dailyValue * $remainingDays;
            
            // 判断操作类型
            if ($currentLevel == $targetLevel) {
                // 同等级，属于续期 - 不抵扣，直接叠加时长
                $result['action_type'] = 'renew';
                $result['discount_amount'] = 0;
                $result['final_price'] = $targetPrice->price;
            } elseif ($currentLevel < $targetLevel) {
                // 升级（假设等级ID越大等级越高）
                $result['action_type'] = 'upgrade';
                // 升级时，剩余价值可以抵扣
                $result['discount_amount'] = round($remainingValue, 2);
                // 计算最终价格
                $result['final_price'] = max(0.01, round($targetPrice->price - $result['discount_amount'], 2));
            }
            
            $result['remaining_days'] = (int)$remainingDays;
            $result['remaining_value'] = round($remainingValue, 2);
            $result['current_cycle'] = $currentCycle;
            $result['current_total_price'] = $currentTotalPrice;
        }
        
        return response()->json($result);
    }

    /**
     * 新增
     * @param UserLevelOrderRequest $request
     * @param UserLevelOrder $userLevelOrder
     * @return UserLevelOrderInfoResource
     * @throws InvalidRequestException|RandomException
     */
    public function store(UserLevelOrderRequest $request, UserLevelOrder $userLevelOrder): UserLevelOrderInfoResource
    {
        $userLevelPriceId = $request->input('user_level_price_id');
        $user = $request->user();

        $userLevelPrice = UserLevelPrice::query()->where('id', $userLevelPriceId)->first();

        if (!$userLevelPrice) {
            throw new InvalidRequestException('购买异常，请重试！');
        }

        $userLevelId = $userLevelPrice->user_level_id;

        $userLevel = UserLevel::query()->where('id', $userLevelId)->first();

        if (!$userLevel) {
            throw new InvalidRequestException('会员等级不存在，请重试！');
        }

        // 计算实际应付金额（考虑续费/升级时的抵扣）
        $cycleDays = [
            0 => 30,   // 月度
            1 => 90,   // 季度
            2 => 365   // 年度
        ];
        
        $actualAmount = $userLevelPrice->price;
        
        // 如果用户有未过期的会员，判断是续费还是升级
        if ($user->user_level_id && $user->expired_at && Carbon::parse($user->expired_at)->isFuture()) {
            $currentLevel = $user->user_level_id;
            $targetLevel = $userLevelPrice->user_level_id;
            
            // 如果是同等级续费，不抵扣，直接使用原价
            if ($currentLevel == $targetLevel) {
                $actualAmount = $userLevelPrice->price;
            } else {
                // 如果是升级，计算剩余价值抵扣
                $expiredAt = Carbon::parse($user->expired_at);
                $now = Carbon::now();
                $remainingDays = $now->diffInDays($expiredAt, false);
                
                if ($remainingDays > 0) {
                    // 获取用户当前会员的价格信息（从最近的订单中获取）
                    $lastOrder = UserLevelOrder::where('user_id', $user->id)
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($lastOrder) {
                        $currentCycle = $lastOrder->cycle;
                        $currentTotalPrice = $lastOrder->total_amount;
                        $currentCycleDays = $cycleDays[$currentCycle] ?? 30;
                        
                        // 计算每日价值
                        $dailyValue = $currentTotalPrice / $currentCycleDays;
                        
                        // 计算剩余价值
                        $remainingValue = $dailyValue * $remainingDays;
                        
                        // 从目标价格中扣除剩余价值
                        $actualAmount = max(0.01, $userLevelPrice->price - $remainingValue);
                    }
                }
            }
        }

        $userLevelOrder->no = UserLevelOrder::generateUniqueNO();
        $userLevelOrder->total_amount = round($actualAmount, 2);
        $userLevelOrder->cycle = $userLevelPrice->cycle;
        $userLevelOrder->user()->associate($user);
        $userLevelOrder->userLevel()->associate($userLevel);
        $userLevelOrder->save();

        return new UserLevelOrderInfoResource($userLevelOrder);
    }
}
