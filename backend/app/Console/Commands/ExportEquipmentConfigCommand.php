<?php

namespace App\Console\Commands;

use App\Services\EquipmentConfigService;
use Illuminate\Console\Command;

class ExportEquipmentConfigCommand extends Command
{
    protected $signature = 'game:export-equipment-config {path? : 输出 JSON 文件路径}';

    protected $description = 'Export equipment, set, gem, blue affix, and purple refinement config to JSON.';

    public function handle(EquipmentConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'storage/app/exports/equipment_config.json',
        );

        $service->exportToJson($path);

        $this->info("装备配置已导出到: {$path}");

        return self::SUCCESS;
    }

    private function normalizePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return $path;
        }

        return base_path($path);
    }
}
