<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "========== 权限系统诊断 ==========\n\n";

// 1. 检查录入员角色
$role = \Spatie\Permission\Models\Role::where('name', '录入员')->first();
if (!$role) {
    echo "❌ 找不到'录入员'角色\n";
    exit(1);
}

echo "✅ 找到角色：{$role->name} (ID: {$role->id})\n";
echo "Guard: {$role->guard_name}\n\n";

// 2. 检查数据库中的权限关联
$dbPermissions = \DB::table('role_has_permissions')
    ->where('role_id', $role->id)
    ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
    ->select('permissions.name')
    ->pluck('name');

echo "数据库中'录入员'角色的权限数量：" . $dbPermissions->count() . "\n";
echo "前10个权限：\n";
$dbPermissions->take(10)->each(fn($p) => print("  - {$p}\n"));

// 3. 检查模型获取的权限
$modelPermissions = $role->permissions;
echo "\n模型获取的'录入员'权限数量：" . $modelPermissions->count() . "\n";

// 4. 检查是否有view_any_house权限
$hasViewHouse = $role->hasPermissionTo('view_any_house');
echo "\n是否有view_any_house权限：" . ($hasViewHouse ? '✅ 是' : '❌ 否') . "\n";

// 5. 检查权限缓存
echo "\n检查权限缓存配置：\n";
$cacheStore = config('permission.cache.store', 'default');
$cacheKey = config('permission.cache.key', 'spatie.permission.cache');
echo "  Cache Store: {$cacheStore}\n";
echo "  Cache Key: {$cacheKey}\n";

// 6. 测试用户权限
$user = \App\Models\AdminUser::where('username', 'dy')->first();
if ($user) {
    echo "\n用户 'dy' 的权限检查：\n";
    echo "  通过角色获取权限数：" . $user->getPermissionsViaRoles()->count() . "\n";
    echo "  直接权限数：" . $user->permissions->count() . "\n";
    echo "  所有权限数：" . $user->getAllPermissions()->count() . "\n";
    echo "  是否有view_any_house：" . ($user->can('view_any_house') ? '✅ 是' : '❌ 否') . "\n";
}

// 7. 检查Filament Shield配置
echo "\n检查Filament Shield配置：\n";
$superAdminName = config('filament-shield.super_admin.name', '超管');
echo "  超管角色名：{$superAdminName}\n";
echo "  是否启用超管：" . (config('filament-shield.super_admin.enabled') ? '是' : '否') . "\n";

echo "\n========== 诊断完成 ==========\n";

