<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\BlueAffix;
use App\Models\Equipment;
use App\Models\EquipmentSet;
use App\Models\Gem;
use App\Models\PlayerEquipment;
use App\Models\PlayerProfile;
use App\Models\PurpleRefinement;
use App\Repositories\Contracts\EquipmentRuntimeRepositoryInterface;
use App\Repositories\Contracts\PlayerRuntimeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EquipmentRuntimeService
{
    public function __construct(
        private readonly EquipmentRuntimeRepositoryInterface $equipmentRuntimeRepository,
        private readonly PlayerRuntimeRepositoryInterface $playerRuntimeRepository,
        private readonly InventoryService $inventoryService,
        private readonly PlayerRuntimeService $playerRuntimeService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getEquipmentDetail(PlayerProfile $playerProfile, string $equipmentUid = ''): array
    {
        $this->ensurePlayerEquipmentInstances($playerProfile);

        return $this->buildEquipmentDetailPayload(
            $this->playerRuntimeRepository->refreshProfile($playerProfile),
            $equipmentUid,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function equip(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $current = $this->equipmentRuntimeRepository->findEquippedBySlotForUpdate((int) $lockedProfile->player_id, (string) $target->slot_type);

            if ($current && $current->equipment_uid !== $target->equipment_uid) {
                $this->equipmentRuntimeRepository->update($current, [
                    'is_equipped' => false,
                ]);
            }

            $this->equipmentRuntimeRepository->update($target, [
                'is_equipped' => true,
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function unequip(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $this->equipmentRuntimeRepository->update($target, [
                'is_equipped' => false,
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function starUp(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $cost = max((int) $target->star_level + (int) config('game_runtime.equipment_runtime.star_up_cost_base', 1), 1);
            $this->inventoryService->consumeItem(
                (int) $lockedProfile->player_id,
                (string) config('game_runtime.equipment_runtime.star_up_item_id', 'material_star_stone'),
                $cost,
                '材料不足',
            );

            $this->equipmentRuntimeRepository->update($target, [
                'star_level' => (int) $target->star_level + 1,
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function socketGem(PlayerProfile $playerProfile, string $equipmentUid, string $gemId, int $slotIndex): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid, $gemId, $slotIndex): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $gem = Gem::query()->where('gem_id', $gemId)->first();

            if (! $gem) {
                throw new ApiException('宝石不存在', 40462, 404);
            }

            $slotLayout = $this->resolveSlotLayout((string) $target->slot_type);

            if (! array_key_exists($slotIndex, $slotLayout)) {
                throw new ApiException('宝石不匹配孔位', 40062, 400);
            }

            $expectedSlotType = (string) $slotLayout[$slotIndex];
            $gemSlotType = (string) ($gem->type === 'boss_core' ? 'boss_core' : 'attribute');

            if ($expectedSlotType !== $gemSlotType) {
                throw new ApiException('宝石不匹配孔位', 40062, 400);
            }

            $gemSlots = $this->normalizeGemSlots($target);
            $existingSlot = $gemSlots[$slotIndex] ?? ['slot_index' => $slotIndex, 'slot_type' => $expectedSlotType, 'gem_id' => null];
            $existingGemId = (string) ($existingSlot['gem_id'] ?? '');

            $this->inventoryService->consumeItem((int) $lockedProfile->player_id, $gemId, 1, '材料不足');

            if ($existingGemId !== '') {
                $this->inventoryService->grantItem((int) $lockedProfile->player_id, $existingGemId, 1);
            }

            $gemSlots[$slotIndex] = [
                'slot_index' => $slotIndex,
                'slot_type' => $expectedSlotType,
                'gem_id' => $gemId,
            ];

            $this->equipmentRuntimeRepository->update($target, [
                'gem_slots_json' => array_values($gemSlots),
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function extractBlueAffix(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $this->inventoryService->consumeItem(
                (int) $lockedProfile->player_id,
                (string) config('game_runtime.equipment_runtime.blue_extract_item_id', 'material_seal_essence'),
                1,
                '材料不足',
            );

            $affixId = (string) BlueAffix::query()->inRandomOrder()->value('affix_id');

            if ($affixId === '') {
                throw new ApiException('蓝词条池为空', 50061, 500);
            }

            $this->equipmentRuntimeRepository->update($target, [
                'blue_affix_id' => $affixId,
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function refinePurpleAffix(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        return DB::transaction(function () use ($playerProfile, $equipmentUid): array {
            $this->ensurePlayerEquipmentInstances($playerProfile);
            $lockedProfile = $this->requireLockedProfile((int) $playerProfile->player_id);
            $target = $this->equipmentRuntimeRepository->findByUidForUpdate((int) $lockedProfile->player_id, $equipmentUid);

            if (! $target) {
                throw new ApiException('装备不存在或未拥有', 40461, 404);
            }

            $this->inventoryService->consumeItem(
                (int) $lockedProfile->player_id,
                (string) config('game_runtime.equipment_runtime.purple_refine_item_id', 'material_refine_sand'),
                1,
                '材料不足',
            );

            $refinementId = (string) PurpleRefinement::query()->inRandomOrder()->value('refinement_id');

            if ($refinementId === '') {
                throw new ApiException('紫洗练池为空', 50062, 500);
            }

            $this->equipmentRuntimeRepository->update($target, [
                'purple_refinement_id' => $refinementId,
            ]);

            $lockedProfile = $this->syncProfileEquipmentSummary($lockedProfile);

            return $this->buildActionPayload($lockedProfile, $equipmentUid);
        });
    }

    public function ensurePlayerEquipmentInstances(PlayerProfile $playerProfile): void
    {
        if ($this->equipmentRuntimeRepository->countByPlayer((int) $playerProfile->player_id) > 0) {
            return;
        }

        $legacySummary = $playerProfile->equipment_summary ?? [];
        $legacyEquipIds = array_values(array_filter(array_map(
            static fn ($entry): string => (string) $entry,
            $legacySummary['equip_ids'] ?? [],
        )));
        $inventory = $this->playerRuntimeRepository->getInventory((int) $playerProfile->player_id);
        $inventoryEquipCounts = [];

        foreach ($inventory as $item) {
            $equipId = (string) $item->item_id;

            if ((int) $item->count <= 0 || ! Equipment::query()->where('equip_id', $equipId)->exists()) {
                continue;
            }

            $inventoryEquipCounts[$equipId] = ($inventoryEquipCounts[$equipId] ?? 0) + (int) $item->count;
        }

        $equippedGemIds = array_values($legacySummary['equipped_gem_ids'] ?? []);
        $blueAffixIds = array_values($legacySummary['blue_affix_ids'] ?? []);
        $purpleRefinementIds = array_values($legacySummary['purple_refinement_ids'] ?? []);

        foreach ($legacyEquipIds as $index => $equipId) {
            $equipment = Equipment::query()->where('equip_id', $equipId)->first();

            if (! $equipment) {
                continue;
            }

            $gemSlots = $this->buildStarterGemSlots((string) $equipment->type, $equippedGemIds);

            $this->equipmentRuntimeRepository->create([
                'equipment_uid' => (string) Str::ulid(),
                'player_id' => (int) $playerProfile->player_id,
                'equip_id' => $equipId,
                'slot_type' => (string) $equipment->type,
                'star_level' => 0,
                'gem_slots_json' => $gemSlots,
                'blue_affix_id' => (string) ($blueAffixIds[$index] ?? ''),
                'purple_refinement_id' => (string) ($purpleRefinementIds[$index] ?? ''),
                'is_equipped' => true,
            ]);
        }

        foreach ($inventoryEquipCounts as $equipId => $count) {
            $equipment = Equipment::query()->where('equip_id', $equipId)->first();

            if (! $equipment) {
                continue;
            }

            for ($index = 0; $index < $count; $index++) {
                $this->equipmentRuntimeRepository->create([
                    'equipment_uid' => (string) Str::ulid(),
                    'player_id' => (int) $playerProfile->player_id,
                    'equip_id' => $equipId,
                    'slot_type' => (string) $equipment->type,
                    'star_level' => 0,
                    'gem_slots_json' => $this->buildEmptyGemSlots((string) $equipment->type),
                    'blue_affix_id' => null,
                    'purple_refinement_id' => null,
                    'is_equipped' => false,
                ]);
            }

            $this->playerRuntimeRepository->incrementItemCount((int) $playerProfile->player_id, $equipId, -$count);
        }

        $this->syncProfileEquipmentSummary($this->playerRuntimeRepository->refreshProfile($playerProfile));
    }

    private function requireLockedProfile(int $playerId): PlayerProfile
    {
        $lockedProfile = $this->playerRuntimeRepository->getProfileForUpdate($playerId);

        if (! $lockedProfile) {
            throw new ApiException('玩家不存在', 40442, 404);
        }

        return $lockedProfile;
    }

    private function syncProfileEquipmentSummary(PlayerProfile $playerProfile): PlayerProfile
    {
        $legacySummary = $playerProfile->equipment_summary ?? [];
        $equipped = $this->equipmentRuntimeRepository->getEquippedByPlayer((int) $playerProfile->player_id);
        $summary = $this->buildEquipmentSummaryFromCollection($equipped, $legacySummary);

        $playerProfile = $this->playerRuntimeRepository->updateProfile($playerProfile, [
            'equipment_summary' => $summary,
        ]);

        return $this->playerRuntimeService->syncComputedFields($playerProfile);
    }

    /**
     * @param  array<string, mixed>  $legacySummary
     * @return array<string, mixed>
     */
    private function buildEquipmentSummaryFromCollection(Collection $equipped, array $legacySummary): array
    {
        $equipIds = [];
        $equippedGemIds = [];
        $equippedBossCoreIds = [];
        $blueAffixIds = [];
        $purpleRefinementIds = [];
        $equippedCounts = [];

        foreach ($equipped as $playerEquipment) {
            $equipIds[] = $playerEquipment->equip_id;
            $equippedCounts[$playerEquipment->equip_id] = ($equippedCounts[$playerEquipment->equip_id] ?? 0) + 1;

            foreach ($playerEquipment->gem_slots_json ?? [] as $slot) {
                $gemId = (string) ($slot['gem_id'] ?? '');

                if ($gemId === '') {
                    continue;
                }

                $gem = Gem::query()->where('gem_id', $gemId)->first();

                if ($gem && $gem->type === 'boss_core') {
                    $equippedBossCoreIds[] = $gemId;
                } else {
                    $equippedGemIds[] = $gemId;
                }
            }

            if ((string) $playerEquipment->blue_affix_id !== '') {
                $blueAffixIds[] = (string) $playerEquipment->blue_affix_id;
            }

            if ((string) $playerEquipment->purple_refinement_id !== '') {
                $purpleRefinementIds[] = (string) $playerEquipment->purple_refinement_id;
            }
        }

        $setCounts = EquipmentSet::query()
            ->orderBy('set_id')
            ->get()
            ->map(function (EquipmentSet $equipmentSet) use ($equippedCounts): array {
                $count = 0;

                foreach ($equipmentSet->pieces ?? [] as $pieceId) {
                    $count += (int) ($equippedCounts[(string) $pieceId] ?? 0);
                }

                return [
                    'set_id' => $equipmentSet->set_id,
                    'equipped_count' => $count,
                ];
            })
            ->filter(static fn (array $entry): bool => (int) $entry['equipped_count'] > 0)
            ->values()
            ->all();

        return [
            'equip_ids' => $equipIds,
            'set_counts' => $setCounts,
            'talisman_star_links' => $legacySummary['talisman_star_links'] ?? [],
            'equipped_boss_core_ids' => array_values(array_unique($equippedBossCoreIds)),
            'equipped_gem_ids' => array_values(array_unique($equippedGemIds)),
            'blue_affix_ids' => array_values(array_unique($blueAffixIds)),
            'purple_refinement_ids' => array_values(array_unique($purpleRefinementIds)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildActionPayload(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        $detail = $this->buildEquipmentDetailPayload($playerProfile, $equipmentUid);
        $initPayload = $this->playerRuntimeService->getInitPayload($playerProfile);

        return array_merge($detail, [
            'player' => $initPayload['player'],
            'inventory' => $initPayload['inventory'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEquipmentDetailPayload(PlayerProfile $playerProfile, string $equipmentUid): array
    {
        $collection = $this->equipmentRuntimeRepository->getByPlayer((int) $playerProfile->player_id);
        $setSummary = $playerProfile->equipment_summary['set_counts'] ?? [];
        $payloadList = $collection
            ->map(fn (PlayerEquipment $playerEquipment): array => $this->buildEquipmentPayload($playerEquipment, $setSummary))
            ->values()
            ->all();
        $selected = collect($payloadList)->firstWhere('equipment_uid', $equipmentUid);

        if (! is_array($selected)) {
            $selected = collect($payloadList)->firstWhere('is_equipped', true);
        }

        if (! is_array($selected)) {
            $selected = $payloadList[0] ?? [];
        }

        $equippedSlots = collect($payloadList)
            ->filter(static fn (array $entry): bool => (bool) ($entry['is_equipped'] ?? false))
            ->mapWithKeys(static fn (array $entry): array => [
                (string) $entry['slot_type'] => $entry,
            ])
            ->all();

        return [
            'equipment_list' => $payloadList,
            'selected_equipment' => $selected,
            'equipped_slots' => $equippedSlots,
            'set_summary' => $setSummary,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $setSummary
     * @return array<string, mixed>
     */
    private function buildEquipmentPayload(PlayerEquipment $playerEquipment, array $setSummary): array
    {
        $template = Equipment::query()->where('equip_id', $playerEquipment->equip_id)->first();
        $gemSlots = $this->normalizeGemSlots($playerEquipment);
        $blueAffix = (string) $playerEquipment->blue_affix_id !== ''
            ? BlueAffix::query()->where('affix_id', $playerEquipment->blue_affix_id)->first()
            : null;
        $purpleRefinement = (string) $playerEquipment->purple_refinement_id !== ''
            ? PurpleRefinement::query()->where('refinement_id', $playerEquipment->purple_refinement_id)->first()
            : null;
        $bonusAtk = 0;
        $bonusDef = 0;
        $bonusBossDmg = 0;

        foreach ($gemSlots as $slot) {
            $gemId = (string) ($slot['gem_id'] ?? '');
            $gem = $gemId !== '' ? Gem::query()->where('gem_id', $gemId)->first() : null;

            if (! $gem) {
                continue;
            }

            $bonusAtk += (int) $gem->bonus_atk;
            $bonusBossDmg += (int) $gem->bonus_boss_dmg;
        }

        foreach (($blueAffix?->bonuses ?? []) as $key => $value) {
            if ($key === 'bonus_atk') {
                $bonusAtk += (int) $value;
            }

            if ($key === 'bonus_def') {
                $bonusDef += (int) $value;
            }
        }

        foreach (($purpleRefinement?->bonuses ?? []) as $key => $value) {
            if ($key === 'bonus_atk') {
                $bonusAtk += (int) $value;
            }

            if ($key === 'bonus_def') {
                $bonusDef += (int) $value;
            }

            if ($key === 'bonus_boss_dmg') {
                $bonusBossDmg += (int) $value;
            }
        }

        $baseAtk = (int) ($template?->base_atk ?? 0);
        $baseDef = (int) ($template?->base_def ?? 0);
        $starRatio = 1 + ((int) $playerEquipment->star_level * 0.1);
        $finalAtk = (int) round($baseAtk * $starRatio) + $bonusAtk;
        $finalDef = (int) round($baseDef * $starRatio) + $bonusDef;

        return [
            'equipment_uid' => $playerEquipment->equipment_uid,
            'equip_id' => $playerEquipment->equip_id,
            'name' => $template?->name ?? $playerEquipment->equip_id,
            'type' => $template?->type ?? $playerEquipment->slot_type,
            'level' => (int) ($template?->level ?? 1),
            'slot_type' => $playerEquipment->slot_type,
            'is_equipped' => (bool) $playerEquipment->is_equipped,
            'star_level' => (int) $playerEquipment->star_level,
            'base_atk' => $baseAtk,
            'base_def' => $baseDef,
            'final_atk' => $finalAtk,
            'final_def' => $finalDef,
            'bonus_boss_dmg' => $bonusBossDmg,
            'gem_slots' => $gemSlots,
            'blue_affix' => $blueAffix ? [
                'affix_id' => $blueAffix->affix_id,
                'name' => $blueAffix->name,
                'bonuses' => $blueAffix->bonuses ?? [],
            ] : null,
            'purple_refinement' => $purpleRefinement ? [
                'refinement_id' => $purpleRefinement->refinement_id,
                'name' => $purpleRefinement->name,
                'bonuses' => $purpleRefinement->bonuses ?? [],
            ] : null,
            'set_summary' => $setSummary,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeGemSlots(PlayerEquipment $playerEquipment): array
    {
        $slotLayout = $this->resolveSlotLayout((string) $playerEquipment->slot_type);
        $stored = [];

        foreach ($playerEquipment->gem_slots_json ?? [] as $slot) {
            $stored[(int) ($slot['slot_index'] ?? 0)] = $slot;
        }

        $result = [];

        foreach ($slotLayout as $index => $slotType) {
            $slot = $stored[$index] ?? [];
            $result[] = [
                'slot_index' => $index,
                'slot_type' => $slotType,
                'gem_id' => (string) ($slot['gem_id'] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildStarterGemSlots(string $slotType, array &$equippedGemIds): array
    {
        $slots = $this->buildEmptyGemSlots($slotType);

        foreach ($slots as $index => $slot) {
            $expectedType = (string) ($slot['slot_type'] ?? 'attribute');
            $matchedIndex = null;

            foreach ($equippedGemIds as $gemIndex => $gemId) {
                $gem = Gem::query()->where('gem_id', (string) $gemId)->first();
                $gemType = $gem && $gem->type === 'boss_core' ? 'boss_core' : 'attribute';

                if ($gemType === $expectedType) {
                    $matchedIndex = $gemIndex;
                    $slots[$index]['gem_id'] = (string) $gemId;
                    break;
                }
            }

            if ($matchedIndex !== null) {
                unset($equippedGemIds[$matchedIndex]);
                $equippedGemIds = array_values($equippedGemIds);
            }
        }

        return $slots;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildEmptyGemSlots(string $slotType): array
    {
        $slots = [];

        foreach ($this->resolveSlotLayout($slotType) as $index => $slotTypeName) {
            $slots[] = [
                'slot_index' => $index,
                'slot_type' => $slotTypeName,
                'gem_id' => '',
            ];
        }

        return $slots;
    }

    /**
     * @return list<string>
     */
    private function resolveSlotLayout(string $slotType): array
    {
        $slotLayouts = config('game_runtime.equipment_runtime.slot_layouts', []);

        if (is_array($slotLayouts[$slotType] ?? null)) {
            return array_values($slotLayouts[$slotType]);
        }

        return array_values($slotLayouts['default'] ?? ['attribute']);
    }
}
