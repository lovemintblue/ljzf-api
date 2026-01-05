<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 每天凌晨1点检查会员到期状态
Schedule::command('vip:check-expiration')->dailyAt('01:00');

// 每天凌晨2点检查并发布预约上架的房源
Schedule::command('houses:publish-scheduled')->dailyAt('02:00');

// 每天凌晨3点检查并关闭到期的推广房源
Schedule::command('houses:expire-top')->dailyAt('03:00');
