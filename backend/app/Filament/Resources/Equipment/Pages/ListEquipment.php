<?php

namespace App\Filament\Resources\Equipment\Pages;

use App\Filament\Resources\Equipment\EquipmentResource;
use App\Services\EquipmentConfigService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEquipment extends ListRecords
{
    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('新增装备'),
            Action::make('import_json')
                ->label('导入 JSON')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->form([
                    TextInput::make('path')
                        ->label('JSON 路径')
                        ->required()
                        ->default('database/seeders/data/equipment_config.json'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, EquipmentConfigService $service): void {
                    $service->importFromJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('装备配置导入成功')
                        ->body('装备、套装、宝石、蓝词条与紫洗练配置已按 JSON 全量覆盖。')
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
                        ->default('storage/app/exports/equipment_config.json'),
                ])
                ->action(function (array $data, EquipmentConfigService $service): void {
                    $path = $service->exportToJson($this->normalizePath($data['path']));

                    Notification::make()
                        ->success()
                        ->title('装备配置导出成功')
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
