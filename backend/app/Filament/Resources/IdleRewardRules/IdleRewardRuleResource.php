<?php

namespace App\Filament\Resources\IdleRewardRules;

use App\Filament\Resources\IdleRewardRules\Pages\CreateIdleRewardRule;
use App\Filament\Resources\IdleRewardRules\Pages\EditIdleRewardRule;
use App\Filament\Resources\IdleRewardRules\Pages\ListIdleRewardRules;
use App\Filament\Resources\IdleRewardRules\Schemas\IdleRewardRuleForm;
use App\Filament\Resources\IdleRewardRules\Tables\IdleRewardRulesTable;
use App\Models\IdleRewardRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IdleRewardRuleResource extends Resource
{
    protected static ?string $model = IdleRewardRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationLabel(): string
    {
        return '挂机收益规则';
    }

    public static function getNavigationGroup(): ?string
    {
        return '玩法配置';
    }

    public static function getModelLabel(): string
    {
        return '挂机收益规则';
    }

    public static function getPluralModelLabel(): string
    {
        return '挂机收益规则';
    }

    public static function form(Schema $schema): Schema
    {
        return IdleRewardRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdleRewardRulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdleRewardRules::route('/'),
            'create' => CreateIdleRewardRule::route('/create'),
            'edit' => EditIdleRewardRule::route('/{record}/edit'),
        ];
    }
}
