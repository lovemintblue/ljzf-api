<div class="px-3 py-4">
    @switch((int)$getState())
        @case(0)
            <x-filament::badge color="warning">
                审核中
            </x-filament::badge>
            @break;
        @case(1)
            <x-filament::badge color="success">
                已通过
            </x-filament::badge>
            @break;
        @case(2)
            <x-filament::badge color="danger">
                已驳回
            </x-filament::badge>
            @break;
    @endswitch
</div>
