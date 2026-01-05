<div class="px-4 py-3">
    <div class="flex flex-row">
        @if($getState() && is_array($getState()))
            @foreach($getState() as $item)
                @if($item === 0)
                    <div>
                        <x-filament::badge>
                            房源
                        </x-filament::badge>
                    </div>
                @endif
                @if($item === 1)
                    <div style="margin-left: 2px">
                        <x-filament::badge>
                            商铺
                        </x-filament::badge>
                    </div>
                @endif
            @endforeach
        @else
            <div>
                <x-filament::badge color="gray">
                    未设置
                </x-filament::badge>
            </div>
        @endif
    </div>
</div>
