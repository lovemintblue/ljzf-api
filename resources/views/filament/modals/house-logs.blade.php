@php
    use App\Models\HouseOperationLog;
    use App\Models\HouseFollowUp;
    use App\Models\AdminUser;
    use App\Models\User;
    
    // 获取操作日志
    $operationLogs = HouseOperationLog::where('house_id', $house->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($log) {
            $log->record_type = 'operation';
            return $log;
        });
    
    // 获取跟进记录
    $followUps = HouseFollowUp::where('house_id', $house->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($log) {
            $log->record_type = 'followup';
            // 将 user_id 映射为 operator_id，以便统一处理
            $log->operator_id = $log->user_id;
            $log->operator_type = 'user';
            // 为跟进记录添加操作类型相关字段
            $log->operation_type = 'followup';
            $log->operation_type_name = '跟进记录';
            $log->operation_type_color = match($log->result) {
                '已出租' => 'success',
                '无人接听' => 'warning',
                '已租' => 'danger',
                '价格不合适' => 'info',
                default => 'gray'
            };
            $log->operation_type_icon = 'heroicon-o-chat-bubble-left-right';
            return $log;
        });
    
    // 合并并按时间排序
    $allLogs = $operationLogs->concat($followUps)->sortByDesc('created_at');
    
    // 静态缓存操作人对象
    $operatorCache = [];
    
    function getOperator($operatorId, $operatorType) {
        global $operatorCache;
        $key = $operatorType . '_' . $operatorId;
        
        if (isset($operatorCache[$key])) {
            return $operatorCache[$key];
        }
        
        if ($operatorType === 'admin') {
            $operatorCache[$key] = AdminUser::find($operatorId);
        } else {
            $operatorCache[$key] = User::find($operatorId);
        }
        
        return $operatorCache[$key];
    }
    
    function getOperationTypeName($log) {
        if ($log->record_type === 'followup') {
            return '跟进记录';
        }
        return match($log->operation_type) {
            'publish' => '首次发布',
            'online' => '重新上架',
            'update' => '更新排序',
            'offline' => '下架',
            default => '未知操作'
        };
    }
    
    function getOperationTypeColor($log) {
        if ($log->record_type === 'followup') {
            return match($log->result) {
                '已出租' => 'success',
                '无人接听' => 'warning',
                '已租' => 'danger',
                '价格不合适' => 'info',
                default => 'gray'
            };
        }
        return match($log->operation_type) {
            'publish' => 'success',
            'online' => 'success',
            'update' => 'info',
            'offline' => 'danger',
            default => 'gray'
        };
    }
    
    function getContentText($log) {
        if ($log->record_type === 'followup') {
            return $log->result ?? '-';
        }
        if ($log->operation_type === 'publish') {
            return '首次发布';
        } elseif ($log->operation_type === 'online') {
            return '重新上架';
        } elseif ($log->operation_type === 'update') {
            return '更新排序';
        } elseif ($log->operation_type === 'offline') {
            return $log->reason ?? '下架';
        }
        return $log->reason ?? '-';
    }
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <thead class="border-b-2 border-gray-300 dark:border-gray-600">
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">操作类型</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">操作人</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">内容</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">时间</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
            @forelse($allLogs as $log)
                @php
                    $operator = getOperator($log->operator_id, $log->operator_type ?? 'user');
                    $typeName = getOperationTypeName($log);
                    $typeColor = getOperationTypeColor($log);
                    $operatorType = ($log->operator_type ?? 'user') === 'admin' ? '管理员' : ($operator?->is_staff ? '员工' : '用户');
                    $operatorName = ($log->operator_type ?? 'user') === 'admin' 
                        ? ($operator?->name ?? $operator?->username ?? '未知管理员')
                        : ($operator?->nickname ?? $operator?->phone ?? '未知用户');
                    $operatorPhone = ($log->operator_type ?? 'user') === 'admin' 
                        ? ($operator?->username ?? '')
                        : ($operator?->phone ?? '');
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors border-l-4
                    @if($typeColor === 'success') border-green-500
                    @elseif($typeColor === 'warning') border-yellow-500
                    @elseif($typeColor === 'danger') border-red-500
                    @elseif($typeColor === 'info') border-blue-500
                    @else border-gray-400
                    @endif">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold shadow-sm
                            @if($typeColor === 'success') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 ring-1 ring-green-600/20
                            @elseif($typeColor === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 ring-1 ring-yellow-600/20
                            @elseif($typeColor === 'danger') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 ring-1 ring-red-600/20
                            @elseif($typeColor === 'info') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ring-1 ring-blue-600/20
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 ring-1 ring-gray-600/20
                            @endif">
                            @if($typeColor === 'success')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @elseif($typeColor === 'danger')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            @elseif($typeColor === 'warning')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            @elseif($typeColor === 'info')
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            @endif
                            {{ $typeName }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($operator && ($log->operator_type ?? 'user') === 'user' && $operator->avatar)
                                @php
                                    $avatarUrl = $operator->avatar;
                                    // 如果不是完整URL，拼接七牛云域名
                                    if (!str_starts_with($avatarUrl, 'http')) {
                                        $qiniuDomain = config('filesystems.disks.qiniu.domain');
                                        // 修复可能的格式问题
                                        $qiniuDomain = str_replace('https//', 'https://', $qiniuDomain);
                                        $qiniuDomain = str_replace('http//', 'http://', $qiniuDomain);
                                        // 检查域名是否已包含协议
                                        if (str_starts_with($qiniuDomain, 'http://') || str_starts_with($qiniuDomain, 'https://')) {
                                            $avatarUrl = rtrim($qiniuDomain, '/') . '/' . ltrim($avatarUrl, '/');
                                        } else {
                                            $avatarUrl = 'https://' . $qiniuDomain . '/' . ltrim($avatarUrl, '/');
                                        }
                                    }
                                @endphp
                                <img src="{{ $avatarUrl }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700 shadow-sm" alt="">
                            @else
                                @php
                                    $firstChar = mb_substr($operatorName, 0, 1);
                                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="18" fill="#3b82f6"/>
                                        <text x="18" y="23" text-anchor="middle" fill="white" font-size="15" font-weight="bold" font-family="Arial">' . htmlspecialchars($firstChar) . '</text>
                                    </svg>';
                                @endphp
                                <img src="data:image/svg+xml;base64,{{ base64_encode($svg) }}" class="w-9 h-9 rounded-full ring-2 ring-gray-200 dark:ring-gray-700 shadow-sm" alt="">
                            @endif
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $operatorName }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                        {{ $operatorType }}
                                    </span>
                                    @if($operatorPhone)
                                        <span class="mx-1">·</span>
                                        <span>{{ $operatorPhone }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-gray-900 dark:text-gray-100 font-medium">{{ getContentText($log) }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col">
                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $log->created_at->format('Y-m-d') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div class="text-gray-500 dark:text-gray-400 font-medium">暂无记录</div>
                            <div class="text-xs text-gray-400 dark:text-gray-500">该房源还没有任何跟进或操作记录</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

