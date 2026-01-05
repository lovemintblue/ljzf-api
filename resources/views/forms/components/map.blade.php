{{-- 引入腾讯地图SDK --}}
<x-dynamic-component
    x-load
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getScriptSrc('tencent-map-sdk') }}"
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.$entangle('{{ $getStatePath() }}'),
            map: null,
            marker: null,

            // 使用箭头函数确保正确的this指向
            initMap: async function() {
                // 等待SDK加载完成
                await this.waitForMapSdk();

                // 确保容器元素存在
                const container = document.getElementById('container');
                if (!container) {
                    console.error('地图容器元素不存在');
                    return;
                }

                // 设置中心点坐标
                const center = new TMap.LatLng(39.984104, 116.307503);

                // 初始化地图
                this.map = new TMap.Map('container', {
                    center: center,
                    zoom: 13
                });

                // 添加点击事件监听
                this.map.on('click', (e) => {
                console.log('12313123')
                    const position = e.latLng;
                    this.state = {
                        latitude: position.lat,
                        longitude: position.lng
                    };
                    // 更新位置信息显示
                    document.getElementById('position').textContent =
                        `${position.lat.toFixed(6)}, ${position.lng.toFixed(6)}`;
                    // 更新标记点
                    this.updateMarker(position);
                });


                // 如果已有位置数据，显示标记点
                if (this.state && this.state.latitude && this.state.longitude) {
                    const position = new TMap.LatLng(this.state.latitude, this.state.longitude);
                    this.updateMarker(position);
                    this.map.setCenter(position);
                }
            },

            // 等待地图SDK加载完成
            waitForMapSdk: function() {
                return new Promise((resolve) => {
                    const checkInterval = setInterval(() => {
                        if (window.TMap) {
                            clearInterval(checkInterval);
                            resolve();
                        }
                    }, 200);
                });
            },

            // 更新地图标记点
            updateMarker: function(position) {
                // 如果标记点已存在，更新位置
           new TMap.MultiMarker({
                        position: position,
                        map: this.map
                    });
            }
        }"
        x-init="initMap()"
    >
        <div id="container" class="w-full h-[400px] border border-gray-300 rounded-lg shadow-sm"></div>
        <div class="mt-2 text-sm text-gray-600">
            当前点击坐标为：<span id="position">未选择</span>
        </div>
    </div>
</x-dynamic-component>
