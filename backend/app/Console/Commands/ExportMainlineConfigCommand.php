<?php

namespace App\Console\Commands;

use App\Services\MainlineConfigService;
use Illuminate\Console\Command;

class ExportMainlineConfigCommand extends Command
{
    protected $signature = 'game:export-mainline-config {path? : 输出 JSON 文件路径}';

    protected $description = 'Export mainline chapter, node, and difficulty config to JSON.';

    public function handle(MainlineConfigService $service): int
    {
        $path = $this->normalizePath(
            $this->argument('path') ?: 'storage/app/exports/mainline_config.json',
        );

        $service->exportToJson($path);

        $this->info("主线配置已导出到: {$path}");

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
