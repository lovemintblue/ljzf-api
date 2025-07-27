<?php

namespace App\Filament\Resources\CommunityResource\Pages;

use App\Filament\Resources\CommunityResource;
use App\Models\BusinessDistrict;
use App\Models\Community;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManageCommunities extends ManageRecords
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
            Actions\Action::make('创建')
                ->form([
                    Select::make('business_district_id')
                        ->label('关联商圈')
                        ->options(BusinessDistrict::query()->pluck('name', 'id'))
                        ->native(false),
                    Select::make('keyword')
                        ->label('选择数据')
                        ->native(false)
                        ->searchable()
                        ->columnSpanFull()
                        ->getSearchResultsUsing(function (string $search) {
                            $api = 'https://apis.map.qq.com/ws/place/v1/search';
                            try {
                                $response = Http::get($api, [
                                    'key' => 'CLLBZ-CEXKX-K5R4V-7ZQPA-VIQ33-4EBX5',
                                    'boundary' => 'nearby(25.817816,114.921171,1000,1)',
                                    'filter' => 'category=住宅区',
                                    'keyword' => $search,
                                ]);

                                if ($response->failed()) {
                                    Log::error('API请求失败', ['response' => $response->body()]);
                                    return [];
                                }

                                $data = $response->json()['data'] ?? [];

                                // 关键修改：将API数据转换为Filament期望的格式
                                $data = collect($data)->map(function ($item) {
                                    return [
                                        $item['title'] => $item['title'],
                                    ];
                                })->toArray();
                                return $data;
                            } catch (\Exception $e) {
                                Log::error('API请求异常', ['message' => $e->getMessage()]);
                                return [];
                            }
                        })
                ])
                ->action(function (array $data): void {
                    $businessDistrictId = $data['business_district_id'] ?? 0;
                    $community = new Community();
                    $community->business_district_id = $businessDistrictId;
                    $keyword = $data['keyword'];
                    $api = 'https://apis.map.qq.com/ws/place/v1/search';
                    $response = Http::get($api, [
                        'key' => 'CLLBZ-CEXKX-K5R4V-7ZQPA-VIQ33-4EBX5',
                        'boundary' => 'nearby(25.817816,114.921171,1000,1)',
                        'filter' => 'category=住宅区',
                        'keyword' => $keyword,
                    ]);
                    $data = $response->json()['data'] ?? [];
                    $data = $data[0];

                    Log::info($data);

                    $community->name = $data['title'];
                    $community->province = $data['ad_info']['province'];
                    $community->city = $data['ad_info']['city'];
                    $community->district = $data['ad_info']['district'];
                    $community->address = $data['address'];
                    $community->longitude = $data['location']['lng'];
                    $community->latitude = $data['location']['lat'];
                    $community->save();

                    Notification::make()->title('创建成功')->success()->send();
                })
        ];
    }
}
