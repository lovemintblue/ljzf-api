<?php
/**
 * 支付 Controller
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLevelOrder;
use App\Notifications\UserNotification;
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
            $userLevelOrder = UserLevelOrder::query()
                ->with('userLevel')
                ->where('no', $message['out_trade_no'])
                ->first();

            $user = User::query()->where('id', $userLevelOrder->user_id)->first();
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
                'expired_at' => $expireAt,
            ]);

            $title = "尊敬的{$user->nickname}，恭喜您成功开通乐家租房{$userLevelOrder->userLevel->name}会员！";
            $content = '开通时间:' . Carbon::now();
            $content .= '到期时间:' . $expireAt;
            $content .= '
即日起您可享平台所属**会员权益，可在小程序【我的】页面点击“我的会员”查看详情与权益使用说明。如有任何疑问，欢迎随时联系客服，我们将竭诚为您服务。感谢您的支持，期待为您带来更多优质体验！';

            $user->notify(new UserNotification([
                'title' => $title,
                'content' => $content
            ]));

        });
        return $server->serve();
    }
}
