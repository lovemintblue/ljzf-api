<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use App\Models\House;
use App\Models\HouseOperationLog;
use Asmit\ResizedColumn\HasResizableColumn;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ManageHouses extends ManageRecords
{
    use HasResizableColumn;

    protected static string $resource = HouseResource::class;

    /**
     * @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportLandlordPhones')
                ->label('导出房东号码')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole('超管'))
                ->requiresConfirmation()
                ->modalHeading('确认导出')
                ->modalDescription('将导出当前筛选条件下的所有房源的房东电话号码（txt格式，不去重）')
                ->modalSubmitActionLabel('确认导出')
                ->action(function () {
                    // 获取当前表格的筛选查询
                    $query = $this->getFilteredTableQuery();
                    
                    // 获取所有符合条件的房源的联系电话
                    $phones = $query->whereNotNull('contact_phone')
                        ->pluck('contact_phone')
                        // ->unique()  // 临时关闭去重
                        // ->sort()    // 临时关闭排序
                        ->values();

                    if ($phones->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('没有数据')
                            ->body('当前筛选条件下没有符合条件的房源')
                            ->send();
                        return;
                    }

                    // 生成文件内容
                    $content = $phones->implode("\n");
                    
                    // 生成文件名
                    $filename = '房东号码_' . now()->format('Y-m-d_His') . '.txt';
                    
                    // 保存到临时目录
                    $path = 'exports/' . $filename;
                    Storage::disk('local')->put($path, $content);
                    
                    // 下载文件
                    $fullPath = Storage::disk('local')->path($path);
                    
                    Notification::make()
                        ->success()
                        ->title('导出成功')
                        ->body("共导出 {$phones->count()} 个房东号码")
                        ->send();

                    // 返回下载响应
                    return response()->download($fullPath, $filename)->deleteFileAfterSend();
                }),
        ];
    }
}
