<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorizationRequest;
use App\Models\User;
use App\Services\MiniAppService;
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
        $iv = $request->input('iv');
        $encryptedData = $request->input('encrypted_data');
        $app = (new MiniAppService())->getApp();
        $utils = $app->getUtils();
        $session = $utils->codeToSession($code);
        $openid = $session['openid'];
        $sessionKey = $session['session_key'];
        $session = $utils->decryptSession($sessionKey, $iv, $encryptedData);

        // 查询用户是否存在
        $user = User::query()->where('mini_app_openid', $openid)->first();
        if (!$user) {
            $user = new User();
            $user->mini_app_openid = $openid;
            $user->phone = $session['phoneNumber'];
            $user->nickname = '微信用户';
            $user->save();
        }
        // 生成 token 令牌
        $token = $user->createToken($user->id)->plainTextToken;
        return response()->json([
            'token' => $token
        ]);
    }
}
