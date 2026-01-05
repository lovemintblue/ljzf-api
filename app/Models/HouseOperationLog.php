<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 房源操作日志模型
 */
class HouseOperationLog extends Model
{
    protected $table = 'house_operation_logs';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * 关联房源
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * 关联操作人（管理员）
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(\Filament\Models\Contracts\FilamentUser::class, 'operator_id');
    }

    /**
     * 获取操作类型显示名称
     */
    public function getOperationTypeNameAttribute(): string
    {
        return match($this->operation_type) {
            'publish' => '首次发布',
            'offline' => '下架',
            'update' => '更新排序',
            'online' => '重新上架',
            default => $this->operation_type,
        };
    }

    /**
     * 获取操作类型颜色
     */
    public function getOperationTypeColorAttribute(): string
    {
        return match($this->operation_type) {
            'publish' => 'success',
            'offline' => 'danger',
            'update' => 'info',
            'online' => 'success',
            default => 'gray',
        };
    }

    /**
     * 获取操作类型图标
     */
    public function getOperationTypeIconAttribute(): string
    {
        return match($this->operation_type) {
            'publish' => 'heroicon-o-rocket-launch',
            'offline' => 'heroicon-o-arrow-down',
            'update' => 'heroicon-o-arrow-path',
            'online' => 'heroicon-o-arrow-up',
            default => 'heroicon-o-document',
        };
    }
}

