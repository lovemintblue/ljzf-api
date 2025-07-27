@php use App\Models\BusinessDistrict; @endphp
<div class="px-3 py-3">
    @foreach(BusinessDistrict::query()->whereIn('id', $getState())->get() as $item)
        <div>
            <x-filament::badge>
                {{$item->name ?? ''}}
            </x-filament::badge>
        </div>
    @endforeach

</div>
