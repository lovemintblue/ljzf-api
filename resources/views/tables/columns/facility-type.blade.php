<div class="px-4 py-3">
    @foreach($getState() as $item)
        <div class="flex flex-row space-x-2">
            @if($item === 0)
                <div>
                    <x-filament::badge>
                        房源
                    </x-filament::badge>
                </div>
            @endif
            @if($item === 1)
                <div>
                    <x-filament::badge>
                        商铺
                    </x-filament::badge>
                </div>
            @endif
        </div>
    @endforeach
</div>
