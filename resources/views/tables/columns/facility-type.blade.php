<div class="px-4 py-3">
    <div class="flex flex-row">
        @foreach($getState() as $item)
            @if((int)$item === 0)
                <div>
                    <x-filament::badge>
                        房源
                    </x-filament::badge>
                </div>
            @endif
            @if((int)$item === 1)
                <div style="margin-left: 2px">
                    <x-filament::badge>
                        商铺
                    </x-filament::badge>
                </div>
            @endif
        @endforeach
    </div>
</div>
