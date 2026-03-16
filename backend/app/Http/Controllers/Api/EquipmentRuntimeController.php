<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentDetailRequest;
use App\Http\Requests\EquipmentEquipRequest;
use App\Http\Requests\EquipmentExtractBlueAffixRequest;
use App\Http\Requests\EquipmentRefinePurpleAffixRequest;
use App\Http\Requests\EquipmentSocketGemRequest;
use App\Http\Requests\EquipmentStarUpRequest;
use App\Http\Requests\EquipmentUnequipRequest;
use App\Models\PlayerProfile;
use App\Services\EquipmentRuntimeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class EquipmentRuntimeController extends Controller
{
    public function __construct(
        private readonly EquipmentRuntimeService $equipmentRuntimeService,
    ) {}

    public function detail(EquipmentDetailRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->getEquipmentDetail(
                $playerProfile,
                (string) $request->validated('equipment_uid', ''),
            ),
        );
    }

    public function equip(EquipmentEquipRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->equip($playerProfile, (string) $request->validated('equipment_uid')),
        );
    }

    public function unequip(EquipmentUnequipRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->unequip($playerProfile, (string) $request->validated('equipment_uid')),
        );
    }

    public function starUp(EquipmentStarUpRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->starUp($playerProfile, (string) $request->validated('equipment_uid')),
        );
    }

    public function socketGem(EquipmentSocketGemRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');
        $validated = $request->validated();

        return ApiResponse::success(
            $this->equipmentRuntimeService->socketGem(
                $playerProfile,
                (string) $validated['equipment_uid'],
                (string) $validated['gem_id'],
                (int) $validated['slot_index'],
            ),
        );
    }

    public function extractBlueAffix(EquipmentExtractBlueAffixRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->extractBlueAffix($playerProfile, (string) $request->validated('equipment_uid')),
        );
    }

    public function refinePurpleAffix(EquipmentRefinePurpleAffixRequest $request): JsonResponse
    {
        /** @var PlayerProfile $playerProfile */
        $playerProfile = $request->attributes->get('playerProfile');

        return ApiResponse::success(
            $this->equipmentRuntimeService->refinePurpleAffix($playerProfile, (string) $request->validated('equipment_uid')),
        );
    }
}
