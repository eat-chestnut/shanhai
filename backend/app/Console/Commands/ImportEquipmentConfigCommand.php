<?php

namespace App\Console\Commands;

use App\Services\EquipmentConfigService;
use Illuminate\Console\Command;

class ImportEquipmentConfigCommand extends Command
{
    protected $signature = 'game:import-equipment-config {path? : 输入 JSON 文件路径}';

    protected $description = 'Import equipment, set, gem, blue affix, and purple refinement config from JSON.';

    public function handle(EquipmentConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'database/seeders/data/equipment_config.json',
        );

        $result = $service->importFromJson($path);

        $this->info(
            "装备配置导入完成: 装备 {$result['equipment']} 条, 套装 {$result['sets']} 条, 宝石 {$result['gems']} 条, 蓝词条 {$result['blue_affixes']} 条, 紫洗练 {$result['purple_refinements']} 条。",
        );

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
