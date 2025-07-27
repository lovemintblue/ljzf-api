@php use App\Models\BusinessDistrict; @endphp
<div class="px-3 py-3">
    <div class="flex flex-row">
        @if(!empty($getState()))
            @foreach(BusinessDistrict::query()->whereIn('id', $getState())->get() as $item)
                <div class="ml-2">
                    <x-filament::badge>
                        {{$item->name ?? ''}}
                    </x-filament::badge>
                </div>
            @endforeach
        @endif
    </div>
</div>
