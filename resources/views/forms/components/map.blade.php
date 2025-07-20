{{-- 引入腾讯地图SDK --}}
@script
<script src="https://map.qq.com/api/gljs?v=1.exp&key=OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77"></script>
@endscript
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            map: null,
            marker: null,

            // 初始化地图（确保 SDK 加载完成）
            initMap() {
                // 循环等待 SDK 加载完成（每 300ms 检查一次）
                if (!window.tencentMapLoaded) {
                    console.log('等待腾讯地图 SDK 加载...');
                    setTimeout(() => this.initMap(), 300); // 递归等待
                    return;
                }

                // SDK 已加载，执行地图初始化
                const mapContainer = $el.querySelector('div'); // 获取地图容器
                this.map = new qq.maps.Map(mapContainer, {
                    center: new qq.maps.LatLng({{ $defaultLat }}, {{ $defaultLng }}),
                    zoom: 13
                });

                // 处理已有经纬度数据（如果存在）
                if (this.state?.latitude && this.state?.longitude) {
                    const position = new qq.maps.LatLng(this.state.latitude, this.state.longitude);
                    this.marker = new qq.maps.Marker({
                        position: position,
                        map: this.map
                    });
                    this.map.setCenter(position); // 定位到已有坐标
                }

                // 监听地图点击事件，更新标记和状态
                const self = this;
                qq.maps.event.addListener(this.map, 'click', function(event) {
                    const latitude = event.latLng.getLat();
                    const longitude = event.latLng.getLng();
                    self.state = { latitude, longitude }; // 同步到 Livewire

                    // 更新标记点
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
