<?php

namespace Database\Seeders;

use App\Models\ChallengeConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChallengeConfigSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/challenge_config.json');

        if (! is_file($path)) {
            return;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            return;
        }

        $rows = $payload['challenge_config'] ?? [];
        $timestamp = Carbon::now();

        DB::transaction(function () use ($rows, $timestamp): void {
            ChallengeConfig::query()->delete();

            foreach ($rows as $index => $row) {
                if (! is_array($row) || (string) ($row['challenge_id'] ?? '') === '') {
                    continue;
                }

                ChallengeConfig::query()->create([
                    'challenge_id' => (string) $row['challenge_id'],
                    'challenge_name' => (string) ($row['challenge_name'] ?? $row['challenge_id']),
                    'challenge_type' => (string) ($row['challenge_type'] ?? 'tower'),
                    'challenge_desc' => (string) ($row['challenge_desc'] ?? ''),
                    'unlock_level' => max((int) ($row['unlock_level'] ?? 60), 1),
                    'cycle_type' => (string) ($row['cycle_type'] ?? 'weekly'),
                    'floors' => is_array($row['floors'] ?? null) ? $row['floors'] : [],
                    'reward_preview' => is_array($row['reward_preview'] ?? null) ? $row['reward_preview'] : [],
                    'sort' => (int) ($row['sort'] ?? $index),
                    'is_open' => (bool) ($row['is_open'] ?? true),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }
}
