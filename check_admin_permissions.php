<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$username = $argv[1] ?? 'dy';

$admin = \App\Models\AdminUser::where('username', $username)
    ->orWhere('name', $username)
    ->first();

if (!$admin) {
    echo "❌ 未找到用户: {$username}\n\n";
    echo "所有管理员用户：\n";
    \App\Models\AdminUser::all()->each(function($u) {
        echo "  ID: {$u->id}, Name: {$u->name}, Username: {$u->username}\n";
    });
    exit(1);
}

echo "✅ 找到管理员：{$admin->name} (ID: {$admin->id})\n";
echo "用户名：{$admin->username}\n\n";

echo "角色：\n";
if ($admin->roles->isEmpty()) {
    echo "  无角色\n";
} else {
    $admin->roles->each(fn($r) => print("  - {$r->name}\n"));
}

echo "\n所有权限（包含角色继承的）：\n";
$permissions = $admin->getAllPermissions();
if ($permissions->isEmpty()) {
    echo "  无权限\n";
} else {
    $permissions->sortBy('name')->each(fn($p) => print("  - {$p->name}\n"));
}

echo "\n是否有导出房东号码权限：" . ($admin->can('export_landlord_phones_house') ? '✅ 是' : '❌ 否') . "\n";

