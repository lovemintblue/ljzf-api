<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLevelResource\Pages;
use App\Filament\Resources\UserLevelResource\RelationManagers;
use App\Models\UserLevel;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserLevelResource extends Resource
{
    protected static ?string $model = UserLevel::class;

    protected static ?string $navigationIcon = 'heroicon-m-squares-2x2';

    protected static ?string $navigationGroup = '用户';

    protected static ?string $navigationLabel = '用户等级';

    protected static ?string $label = '用户等级';

    protected static ?int $navigationSort = 2;

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名称')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('level')
                    ->label('等级')
                    ->required()
                    ->helperText('数字类型，数字越大等级越高')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('status')
                    ->required()
                    ->label('状态')
                    ->default(1),
                Forms\Components\Select::make('privilege')
                    ->label('特权')
                    ->native(false)
                    ->columnSpanFull()
                    ->multiple()
                    ->options(UserLevel::$privilegeMap),
                Forms\Components\TextInput::make('view_phone_count')
                    ->label('查看电话次数')
                    ->helperText('每日可查看手机号的次数')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default(0),
                TableRepeater::make('userLevelPrices')
                    ->label('价格设置')
                    ->emptyLabel('未设置价格')
                    ->relationship()
                    ->headers([
                        Header::make('时长')->width('150px'),
                        Header::make('原价')->width('150px'),
                        Header::make('价格')->width('150px'),
                    ])
                    ->schema([
                        Forms\Components\Select::make('cycle')
                            ->options([
                                0 => '月',
                                1 => '季',
                                2 => '年'
                            ])
                            ->native(false)
                            ->default(0),
                        Forms\Components\TextInput::make('original_price')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->default(0)
                    ])
                    ->columnSpan('full')
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('名称')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('等级')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('status')
                    ->label('状态'),
            ])
            ->recordUrl(null)
            ->recordAction(null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        \Illuminate\Support\Facades\Log::info('编辑用户等级 - 表单提交数据', $data);
                        return $data;
                    })
                    ->using(function ($record, array $data): \App\Models\UserLevel {
                        \Illuminate\Support\Facades\Log::info('编辑用户等级 - 使用自定义保存逻辑', [
                            'record_id' => $record->id,
                            'data' => $data,
                        ]);
                        
                        // 先保存主表数据
                        $record->fill([
                            'name' => $data['name'],
                            'level' => $data['level'],
                            'status' => $data['status'],
                            'privilege' => $data['privilege'] ?? null,
                            'view_phone_count' => $data['view_phone_count'],
                        ]);
                        $record->save();
                        
                        \Illuminate\Support\Facades\Log::info('编辑用户等级 - 主表保存后', [
                            'id' => $record->id,
                            'view_phone_count' => $record->view_phone_count,
                        ]);
                        
                        // 再处理关系数据
                        if (isset($data['userLevelPrices'])) {
                            $record->userLevelPrices()->delete();
                            foreach ($data['userLevelPrices'] as $price) {
                                $record->userLevelPrices()->create($price);
                            }
                        }
                        
                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserLevels::route('/'),
        ];
    }
}
