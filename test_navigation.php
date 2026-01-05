<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Gate;

$user = \App\Models\AdminUser::where('username', 'dy')->first();

if (!$user) {
    echo "❌ 找不到用户 dy\n";
    exit(1);
}

echo "========== 导航权限测试 ==========\n\n";
echo "用户：{$user->name} ({$user->username})\n";
echo "角色：" . $user->roles->pluck('name')->implode(', ') . "\n\n";

// 测试基础权限
$permissions = [
    'view_any_house' => '查看房源列表',
    'view_any_audit::house' => '查看审核房源',
    'view_any_draft::house' => '查看草稿房源',
    'view_any_hidden::house' => '查看隐藏房源',
    'view_any_pending::update::house' => '查看待更新房源',
    'view_any_house::follow::up' => '查看房源跟进',
];

echo "基础权限测试：\n";
foreach ($permissions as $permission => $label) {
    $has = $user->can($permission);
    $icon = $has ? '✅' : '❌';
    echo "  {$icon} {$label}: {$permission}\n";
}

echo "\n用户所有权限（前20个）：\n";
$allPermissions = $user->getAllPermissions()->take(20);
foreach ($allPermissions as $perm) {
    echo "  - {$perm->name}\n";
}

echo "\n========== 测试完成 ==========\n";

