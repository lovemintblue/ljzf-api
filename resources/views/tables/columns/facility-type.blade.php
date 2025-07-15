<div class="px-4 py-3">
    @foreach($getState() as $item)
        @if($item === 0)
            <x-filament::badge>
                房源
            </x-filament::badge>
        @endif
        @if($item === 1)
            <x-filament::badge>
                商铺
            </x-filament::badge>
        @endif
    @endforeach
</div>
