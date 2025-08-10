<?php
/**
 * 支付 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentsController extends Controller
{
    public function payUserLevelOrderByWechat(Request $request, UserLevelOrder $userLevelOrder)
    {
        $user = $request->user();
        $app = (new \App\Services\PaymentService())->getApp();
        $appId = 'wx2a3e44e8b256b4ea';
        $mchId = (string)$app->getMerchant()->getMerchantId();
//        $amount = $userLevelOrder->total_amount * 100;
        $amount = 10;

        $response = $app->getClient()->postJson("v3/pay/transactions/jsapi", [
            "mchid" => $mchId,
            "out_trade_no" => $userLevelOrder->no,
            "appid" => $appId,
            "description" => '支付' . $userLevelOrder->no,
            "notify_url" => route('payments.pay-user-level-order-by-wechat-notify'),
            "amount" => [
                "total" => (int)$amount,
                "currency" => "CNY"
            ],
            "payer" => [
                "openid" => $user->mini_app_openid,
            ]
        ]);
        $response = $response->toArray(false);

        $prepayId = $response['prepay_id'];
        $signType = 'RSA';
        $utils = $app->getUtils();
        $config = $utils->buildBridgeConfig($prepayId, $appId, $signType);
        return response()->json($config);
    }

    public function payUserLevelOrderByWechatNotify()
    {
        Log::info('--订单支付回调--');
        // $app 为你实例化的支付对象，此处省略实例化步骤
        $app = (new \App\Services\PaymentService())->getApp();
        $server = $app->getServer();
        // 处理支付结果事件
        $server->handlePaid(function ($message) {
            $userLevelOrder = UserLevelOrder::query()->where('no', $message['out_trade_no'])->first();
            $userLevelOrder->update([
                'status' => 1,
            ]);

            // 根据计算周期 计算到期时间
            $expireAt = '';
            if ((int)$userLevelOrder->cycle === 0) {
                $expireAt = Carbon::now()->addMonth();
            }

            if ((int)$userLevelOrder->cycle === 1) {
                $expireAt = Carbon::now()->addMonths(3);
            }

            if ((int)$userLevelOrder->cycle === 2) {
                $expireAt = Carbon::now()->addYear();
            }

            Log::info('到期时间:' . $expireAt);

            User::query()->where('id', $userLevelOrder->user_id)->update([
                'user_level_id' => $userLevelOrder->user_level_id,
                'expire_at' => $expireAt,
            ]);
        });
        return $server->serve();
    }
}
