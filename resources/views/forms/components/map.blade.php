{{-- 引入腾讯地图SDK --}}
@push('scripts')
    <script charset="utf-8" src="https://map.qq.com/api/gljs?v=1.exp&key=OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77"></script>
@endpush
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'), // 与 Livewire 状态绑定
            map: null, // 地图实例
            marker: null // 标记点实例
        }"
        x-init="initMap()"
    >
        {{-- 地图容器 --}}
        <div style="width: 100%; height: 400px; border: 1px solid #e2e8f0; border-radius: 4px;"></div>

        {{-- 经纬度显示（可选，用于调试或用户确认） --}}
        <div class="mt-2 text-sm text-gray-500">
            选中经纬度：
            <span x-text="state ? state.latitude + ', ' + state.longitude : '未选择'"></span>
        </div>
    </div>
</x-dynamic-component>
{{-- 引入腾讯地图SDK --}}
@push('scripts')
    <script>
        function initMap() {
            // 初始化地图（容器为当前组件的第一个 div）
            const mapContainer = $el.querySelector('div');
            this.map = new qq.maps.Map(mapContainer, {
                center: new qq.maps.LatLng({{ $defaultLat }}, {{ $defaultLng }}), // 默认中心点
                zoom: 13 // 缩放级别
            });

            // 初始化标记点（如果已有数据）
            if (this.state?.latitude && this.state?.longitude) {
                const position = new qq.maps.LatLng(this.state.latitude, this.state.longitude);
                this.marker = new qq.maps.Marker({
                    position: position,
                    map: this.map
                });
                this.map.setCenter(position); // 定位到已有坐标
            }

            // 监听地图点击事件，更新标记点和状态
            const self = this;
            qq.maps.event.addListener(this.map, 'click', function (event) {
                const latitude = event.latLng.getLat();
                const longitude = event.latLng.getLng();

                // 更新状态（与 Livewire 同步）
                self.state = {latitude, longitude};

                // 移动或创建标记点
                if (self.marker) {
                    self.marker.setPosition(event.latLng);
                } else {
                    self.marker = new qq.maps.Marker({
                        position: event.latLng,
                        map: self.map
                    });
                }
            });
        }
    </script>
@endpush
