<?php

namespace App\Filament\Resources\RarityConfigs\Schemas;

use App\Models\RarityConfig;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RarityConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('基本信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('rarity_key')
                            ->label('稀有度键值')
                            ->required()
                            ->maxLength(50)
                            ->unique(RarityConfig::class, 'rarity_key', ignoreRecord: true)
                            ->helperText('稀有度的唯一标识符，建议使用英文'),
                        TextInput::make('rarity_name')
                            ->label('稀有度名称')
                            ->required()
                            ->maxLength(50)
                            ->helperText('稀有度的显示名称'),
                        TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0)
                            ->helperText('排序值，数值越小越靠前'),
                        TextInput::make('frame_key')
                            ->label('边框样式键值')
                            ->maxLength(50)
                            ->placeholder('frame_common')
                            ->helperText('边框样式的标识符'),
                    ]),
                
                Section::make('颜色配置')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('text_color')
                            ->label('文字颜色')
                            ->required()
                            ->default('#FFFFFF')
                            ->helperText('物品名称的文字颜色'),
                        ColorPicker::make('bg_color')
                            ->label('背景颜色')
                            ->required()
                            ->default('#2F2F2F')
                            ->helperText('物品背景的颜色'),
                        ColorPicker::make('border_color')
                            ->label('边框颜色')
                            ->required()
                            ->default('#7A7A7A')
                            ->helperText('物品边框的颜色'),
                    ]),
                
                Section::make('状态设置')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('启用状态')
                            ->default(true)
                            ->helperText('是否启用此稀有度配置'),
                    ]),
            ]);
    }
}
