<?php

namespace Database\Seeders;

use App\Models\IdleRewardRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IdleRewardRuleSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/idle_reward_rules.json');

        if (! is_file($path)) {
            return;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            return;
        }

        $rows = $payload['idle_reward_rules'] ?? [];
        $timestamp = Carbon::now();

        DB::transaction(function () use ($rows, $timestamp): void {
            IdleRewardRule::query()->delete();

            foreach ($rows as $index => $row) {
                if (! is_array($row) || (string) ($row['rule_id'] ?? '') === '') {
                    continue;
                }

                IdleRewardRule::query()->create([
                    'rule_id' => (string) $row['rule_id'],
                    'rule_name' => (string) ($row['rule_name'] ?? $row['rule_id']),
                    'min_level' => max((int) ($row['min_level'] ?? 1), 1),
                    'max_level' => max((int) ($row['max_level'] ?? 999), 1),
                    'idle_cap_hours' => max((int) ($row['idle_cap_hours'] ?? 12), 1),
                    'reward_rate' => is_array($row['reward_rate'] ?? null) ? $row['reward_rate'] : [],
                    'bonus_hint' => (string) ($row['bonus_hint'] ?? ''),
                    'sort' => (int) ($row['sort'] ?? $index),
                    'is_open' => (bool) ($row['is_open'] ?? true),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        });
    }
}
