<?php

namespace App\Filament\Resources\MonsterDrops\Schemas;

use App\Enums\MonsterDropKind;
use App\Models\Monster;
use App\Models\MonsterDrop;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class MonsterDropForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FormSection::make('怪物掉落信息')
                    ->columns(2)
                    ->schema([
                        Select::make('monster_id')
                            ->label('monster_id')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->options(fn (): array => Monster::query()
                                ->orderBy('monster_id')
                                ->get()
                                ->mapWithKeys(static fn (Monster $monster): array => [
                                    $monster->monster_id => "{$monster->monster_id} / {$monster->name}",
                                ])
                                ->all())
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                $isBoss = Monster::query()->where('monster_id', $state)->value('is_boss');

                                if (! $isBoss) {
                                    $set('drop_kind', MonsterDropKind::Normal->value);
                                } elseif (($get('drop_kind') ?? MonsterDropKind::Normal->value) === MonsterDropKind::Normal->value) {
                                    $set('drop_kind', MonsterDropKind::BossFixed->value);
                                }
                            }),
                        TextInput::make('item_id')
                            ->label('item_id')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                table: MonsterDrop::class,
                                column: 'item_id',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('monster_id', $get('monster_id')),
                            ),
                        TextInput::make('drop_rate')
                            ->label('drop_rate')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->step('0.0001')
                            ->default(0.1),
                        Select::make('drop_kind')
                            ->label('drop_kind')
                            ->required()
                            ->options(MonsterDropKind::options())
                            ->default(MonsterDropKind::Normal->value)
                            ->native(false)
                            ->rules([
                                Rule::in(MonsterDropKind::values()),
                                function (Get $get) {
                                    return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                        $monsterId = (string) ($get('monster_id') ?? '');
                                        $isBoss = Monster::query()->where('monster_id', $monsterId)->value('is_boss');

                                        if (
                                            in_array($value, [MonsterDropKind::BossFixed->value, MonsterDropKind::BossCore->value], true) &&
                                            ! $isBoss
                                        ) {
                                            $fail('只有 Boss 怪物才能配置 Boss 固定掉落或核心掉落。');
                                        }
                                    };
                                },
                            ])
                            ->helperText('Boss 固定掉落建议使用 1.0；核心掉落可使用较低概率。'),
                    ]),
            ]);
    }
}
