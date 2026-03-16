<?php

namespace Database\Seeders;

use App\Models\TaskConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskConfigSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/task_config.json');

        if (! is_file($path)) {
            return;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            return;
        }

        $rows = $payload['task_config'] ?? [];
        $timestamp = Carbon::now();

        DB::transaction(function () use ($rows, $timestamp): void {
            TaskConfig::query()->delete();

            foreach ($rows as $index => $row) {
                if (! is_array($row) || (string) ($row['task_id'] ?? '') === '') {
                    continue;
                }

                TaskConfig::query()->create([
                    'task_id' => (string) $row['task_id'],
                    'task_type' => (string) ($row['task_type'] ?? 'daily'),
                    'task_name' => (string) ($row['task_name'] ?? $row['task_id']),
                    'task_desc' => (string) ($row['task_desc'] ?? ''),
                    'target_type' => (string) ($row['target_type'] ?? 'battle_complete'),
                    'target' => max((int) ($row['target'] ?? 1), 1),
                    'conditions' => is_array($row['conditions'] ?? null) ? $row['conditions'] : [],
                    'rewards' => is_array($row['rewards'] ?? null) ? $row['rewards'] : [],
                    'sort' => (int) ($row['sort'] ?? $index),
                    'is_open' => (bool) ($row['is_open'] ?? true),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }
}
