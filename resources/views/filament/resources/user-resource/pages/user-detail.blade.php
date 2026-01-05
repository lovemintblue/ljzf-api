<x-filament-panels::page>
    <!-- 用户信息 -->
    {{ $this->infolist }}
    
    <!-- 跟进记录表格 -->
    @if($this->hasFollowUps())
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endif
    
    <x-filament-actions::modals />
</x-filament-panels::page>

