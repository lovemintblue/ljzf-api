<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use FilamentTiptapEditor\TiptapEditor;

class ManageGeneral extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = GeneralSettings::class;

    protected static ?string $navigationGroup = '系统';

    protected static ?string $title = '基础设置';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Tab 1')
                            ->label('会员设置')
                            ->schema([
                                Textarea::make('user_level_benefit')
                                    ->autosize()
                                    ->label('会员权益'),
                                Textarea::make('user_level_rule')
                                    ->autosize()
                                    ->label('会员规则'),
                                Textarea::make('user_level_other')
                                    ->autosize()
                                    ->label('会员其他说明'),
                            ]),
                        Tabs\Tab::make('Tab 2')
                            ->label('加入我们')
                            ->schema([
                                TiptapEditor::make('join_us')
                                    ->profile('default')
                                    ->label('')
                                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                                    ->maxContentWidth('3xl'),
                            ]),
                        Tabs\Tab::make('Tab 3')
                            ->label('公司介绍')
                            ->schema([
                                TiptapEditor::make('company_intro')
                                    ->profile('default')
                                    ->label('')
                                    ->maxContentWidth('3xl')
                                    ->extraInputAttributes(['style' => 'min-height: 12rem;']),
                            ]),
                        Tabs\Tab::make('Tab 4')
                            ->label('隐私协议')
                            ->schema([
                                TiptapEditor::make('privacy_policy')
                                    ->profile('default')
                                    ->label('')
                                    ->maxContentWidth('3xl')
                                    ->extraInputAttributes(['style' => 'min-height: 12rem;']),
                            ]),
                        Tabs\Tab::make('Tab 5')
                            ->label('发布协议')
                            ->schema([
                                TiptapEditor::make('public_agreement')
                                    ->profile('default')
                                    ->label('')
                                    ->maxContentWidth('3xl')
                                    ->extraInputAttributes(['style' => 'min-height: 12rem;']),
                            ]),
                    ])
            ]);
    }
}
