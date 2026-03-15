<?php

namespace App\Console\Commands;

use App\Services\DungeonContentConfigService;
use Illuminate\Console\Command;

class ImportDungeonContentConfigCommand extends Command
{
    protected $signature = 'game:import-dungeon-content-config {path? : 输入 JSON 文件路径}';

    protected $description = 'Import dungeon, monster, and drop config from JSON.';

    public function handle(DungeonContentConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'database/seeders/data/dungeon_content_config.json',
        );

        $result = $service->importFromJson($path);

        $this->info(
            "副本配置导入完成: 副本 {$result['dungeons']} 条, 难度 {$result['difficulties']} 条, 怪物 {$result['monsters']} 条, 掉落 {$result['drops']} 条。",
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
