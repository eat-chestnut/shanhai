<?php

namespace App\Console\Commands;

use App\Services\MainlineConfigService;
use Illuminate\Console\Command;

class ImportMainlineConfigCommand extends Command
{
    protected $signature = 'game:import-mainline-config {path? : 输入 JSON 文件路径}';

    protected $description = 'Import mainline chapter, node, and difficulty config from JSON.';

    public function handle(MainlineConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'database/seeders/data/mainline_config.json',
        );

        $result = $service->importFromJson($path);

        $this->info(
            "主线配置导入完成: 章节 {$result['chapters']} 条, 节点 {$result['nodes']} 条, 难度 {$result['difficulties']} 条。",
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
