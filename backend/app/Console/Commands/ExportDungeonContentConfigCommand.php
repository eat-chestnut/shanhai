<?php

namespace App\Console\Commands;

use App\Services\DungeonContentConfigService;
use Illuminate\Console\Command;

class ExportDungeonContentConfigCommand extends Command
{
    protected $signature = 'game:export-dungeon-content-config {path? : 输出 JSON 文件路径}';

    protected $description = 'Export dungeon, monster, and drop config to JSON.';

    public function handle(DungeonContentConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'storage/app/exports/dungeon_content_config.json',
        );

        $service->exportToJson($path);

        $this->info("副本配置已导出到: {$path}");

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
