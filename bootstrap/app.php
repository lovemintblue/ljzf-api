<?php

use App\Exceptions\InvalidRequestException;
use App\Http\Middleware\AcceptHeader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 设置中间件
        $middleware->api(prepend: [
            // 设置默认请求头
            AcceptHeader::class,
        ]);

        // 注册自定义中间件别名
        $middleware->alias([
            'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // 每天初始化会员查看次数
        $schedule->command('app:init-user-view-phone-count')->daily();

        // 处理会员到期 - 改为每天凌晨执行，避免频繁查询
        $schedule->command('app:handle-user-expired')
            ->daily()  // 每天执行一次
            ->at('00:30')  // 凌晨 00:30 执行（用户访问量低的时候）
            ->withoutOverlapping();  // 防止任务重叠执行
        $schedule->command('app:handle-watermark')->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 不报告的异常
        $exceptions->dontReport([
            InvalidRequestException::class
        ]);

        // 捕获异常
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if ($response->getStatusCode() === 401) {
                return response()->json([
                    'message' => '令牌无效或过期!',
                ])->setStatusCode(401);
            }
            return $response;
        });
    })->create();
