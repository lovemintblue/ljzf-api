<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreateExportLandlordPhonesPermission extends Command
{
    protected $signature = 'permission:create-export-phones';

    protected $description = '创建导出房东号码权限';

    public function handle()
    {
        $permissionName = 'export_landlord_phones_house';
        
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);

        if ($permission->wasRecentlyCreated) {
            $this->info("✅ 权限 '{$permissionName}' 创建成功！");
        } else {
            $this->info("ℹ️  权限 '{$permissionName}' 已存在。");
        }

        // 查找超管角色并自动分配权限
        $superAdminRole = \Spatie\Permission\Models\Role::where('name', '超管')->first();
        if ($superAdminRole) {
            if (!$superAdminRole->hasPermissionTo($permissionName)) {
                $superAdminRole->givePermissionTo($permissionName);
                $this->info("✅ 已为'超管'角色分配此权限！");
            } else {
                $this->info("ℹ️  '超管'角色已有此权限。");
            }
        }

        $this->info("\n现在可以在角色管理中看到此权限了！");
        
        return 0;
    }
}

