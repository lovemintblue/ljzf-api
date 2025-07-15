<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

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
                                // ...
                            ]),
                        Tabs\Tab::make('Tab 2')
                            ->label('加入我们')
                            ->schema([
                                // ...
                            ]),
                        Tabs\Tab::make('Tab 3')
                            ->label('公司介绍')
                            ->schema([
                                // ...
                            ]),
                        Tabs\Tab::make('Tab 4')
                            ->label('隐私协议')
                            ->schema([
                                // ...
                            ]),
                    ])
            ]);
    }
}
