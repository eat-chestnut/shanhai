<?php

namespace App\Filament\Resources\Dungeons\Pages;

use App\Filament\Resources\Dungeons\DungeonResource;
use App\Services\DungeonContentConfigService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDungeons extends ListRecords
{
    protected static string $resource = DungeonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('import_json')
                ->label('导入 JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->form([
                    TextInput::make('path')
                        ->label('JSON 路径')
                        ->required()
                        ->default('database/seeders/data/dungeon_content_config.json'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, DungeonContentConfigService $service): void {
                    $service->importFromJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('副本配置导入成功')
                        ->body('副本、难度、怪物与掉落配置已按 JSON 全量覆盖。')
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                }),
            Action::make('export_json')
                ->label('导出 JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    TextInput::make('path')
                        ->label('输出路径')
                        ->required()
                        ->default('storage/app/exports/dungeon_content_config.json'),
                ])
                ->action(function (array $data, DungeonContentConfigService $service): void {
                    $path = $service->exportToJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('副本配置导出成功')
                        ->body($path)
                        ->send();
                }),
        ];
    }

    private function normalizePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }
}
