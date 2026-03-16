<?php

namespace App\Filament\Resources\ScriptureMonsters\Schemas;

use App\Models\Item;
use App\Models\ScriptureMonster;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScriptureMonsterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('怪物信息')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monster_id')
                            ->label('怪物ID')
                            ->required()
                            ->maxLength(100)
                            ->unique(ScriptureMonster::class, 'monster_id', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('怪物名称')
                            ->required()
                            ->maxLength(100),
                        Select::make('monster_type')
                            ->label('怪物类型')
                            ->required()
                            ->native(false)
                            ->options([
                                'normal' => '普通',
                                'elite' => '精英',
                                'boss' => 'Boss',
                            ]),
                        TextInput::make('race')
                            ->label('种族')
                            ->required()
                            ->maxLength(100),
                        Select::make('rarity')
                            ->label('稀有度')
                            ->required()
                            ->native(false)
                            ->options(Item::getRarityOptions()),
                        TextInput::make('ai_type')
                            ->label('AI类型')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('base_hp')
                            ->label('基础生命值')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('base_atk')
                            ->label('基础攻击力')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('base_def')
                            ->label('基础防御力')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('move_speed')
                            ->label('移动速度')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TagsInput::make('skill_ids')
                            ->label('技能ID列表')
                            ->placeholder('输入 skill_id 后回车')
                            ->helperText('严格按 JSON 中的 skill_ids 保存原始数组。')
                            ->columnSpanFull(),
                        Toggle::make('is_boss')
                            ->label('是否Boss')
                            ->default(false)
                            ->inline(false),
                        Toggle::make('is_elite')
                            ->label('是否精英')
                            ->default(false)
                            ->inline(false),
                        Toggle::make('is_enabled')
                            ->label('是否启用')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
