<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTemplateResource\Pages;
use App\Models\NotificationTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = '通知模板';

    protected static ?string $modelLabel = '通知模板';

    protected static ?string $navigationGroup = '系统设置';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('通知代码')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('系统内部使用的唯一标识，创建后不可修改'),

                        Forms\Components\TextInput::make('name')
                            ->label('通知名称')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_enabled')
                            ->label('是否启用')
                            ->default(true)
                            ->inline(false),
                    ])->columns(3),

                Forms\Components\Section::make('通知内容')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('通知标题')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('content')
                            ->label('通知内容')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull()
                            ->helperText('支持使用变量，如：{nickname}、{time} 等'),

                        Forms\Components\Textarea::make('variables')
                            ->label('可用变量说明')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('格式：变量名:说明，多个变量用逗号分隔。例如：nickname:用户昵称, time:时间'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('通知名称')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('通知代码')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('title')
                    ->label('通知标题')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('启用状态')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('启用状态')
                    ->placeholder('全部')
                    ->trueLabel('已启用')
                    ->falseLabel('已禁用'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationTemplates::route('/'),
            'create' => Pages\CreateNotificationTemplate::route('/create'),
            'edit' => Pages\EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
