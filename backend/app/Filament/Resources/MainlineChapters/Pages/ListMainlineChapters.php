<?php

namespace App\Filament\Resources\MainlineChapters\Pages;

use App\Filament\Resources\MainlineChapters\MainlineChapterResource;
use App\Services\MainlineConfigService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMainlineChapters extends ListRecords
{
    protected static string $resource = MainlineChapterResource::class;

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
                        ->default('database/seeders/data/mainline_config.json'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, MainlineConfigService $service): void {
                    $service->importFromJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('主线配置导入成功')
                        ->body('章节、节点、难度配置已按 JSON 全量覆盖。')
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
                        ->default('storage/app/exports/mainline_config.json'),
                ])
                ->action(function (array $data, MainlineConfigService $service): void {
                    $path = $service->exportToJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('主线配置导出成功')
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
