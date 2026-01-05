<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InvalidRequestException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizationRequest;
use App\Models\User;
use App\Services\MiniAppService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorizationsController extends Controller
{
    /**
     * 登录
     * @param AuthorizationRequest $request
     * @return JsonResponse
     */
    public function store(AuthorizationRequest $request): JsonResponse
    {
        $code = $request->input('code');
        $app = (new MiniAppService())->getApp();
        $utils = $app->getUtils();
        $session = $utils->codeToSession($code);
        $openid = $session['openid'];

        // 查询用户是否存在
        $user = User::query()->where('mini_app_openid', $openid)->first();
        if (!$user) {
            throw new InvalidRequestException('用户未注册');
        }
        // 生成 token 令牌
        $token = $user->createToken($user->id)->plainTextToken;
        
        // 发送登录成功通知
        (new NotificationService())->notifyLoginSuccess($user, $request->ip());
        
        return response()->json([
            'token' => $token
        ]);
    }
    /**
     * 注册
     * @param AuthorizationRequest $request
     * @return JsonResponse
     */
    public function register(AuthorizationRequest $request): JsonResponse
    {
        $code = $request->input('code');
        $iv = $request->input('iv');
        $encryptedData = $request->input('encrypted_data');
        $app = (new MiniAppService())->getApp();
        $utils = $app->getUtils();
        
        try {
            $session = $utils->codeToSession($code);
        } catch (\Exception $e) {
            throw new InvalidRequestException('注册失败，请重新获取授权: ' . $e->getMessage());
        }
        
        $openid = $session['openid'];
        $sessionKey = $session['session_key'];
        $session = $utils->decryptSession($sessionKey, $iv, $encryptedData);
        $nickname = $request->input('nickname');
        $avatar = $request->input('avatar');
        if (empty($avatar)){
            $avatar = '';
        }

        // 查询用户是否存在
        $user = User::query()->where('mini_app_openid', $openid)->first();
        if (!$user) {
            $user = new User();
            $user->mini_app_openid = $openid;
            $user->phone = $session['purePhoneNumber'];
            // 先使用临时昵称，保存后获取ID
            $user->nickname = '用户';
            $user->avatar = $avatar;
            $user->save();
            
            // 如果用户没有提供昵称，使用 "用户{ID}" 格式
            if (empty($nickname)) {
                $user->nickname = '用户' . $user->id;
                $user->save();
            } else {
                $user->nickname = $nickname;
                $user->save();
            }
            
            // 发送注册成功通知
            (new NotificationService())->notifyRegisterSuccess($user);
        } else {
            throw new InvalidRequestException('请勿重复注册');
        }
        // 生成 token 令牌
        $token = $user->createToken($user->id)->plainTextToken;
        return response()->json([
            'token' => $token
        ]);
    }
}
