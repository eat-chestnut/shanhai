<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillApiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'skill_id' => $this->skill_id,
            'class_id' => $this->class_id,
            'skill_name' => $this->skill_name,
            'skill_desc' => $this->skill_desc,
            'type' => $this->type,
            'effect_type' => $this->effect_type,
            'effect' => $this->effect_type,
            'target_type' => $this->target_type,
            'range' => $this->target_type,
            'cooldown' => (int) $this->cooldown,
            'cost' => (int) $this->cost,
            'unlock_level' => (int) $this->unlock_level,
            'max_level' => (int) $this->max_level,
            'power_base' => (int) $this->power_base,
            'damage' => (int) $this->power_base,
            'power_per_level' => (int) $this->power_per_level,
            'duration' => (int) $this->duration,
            'chance' => (float) $this->chance,
            'stat_bonuses' => $this->stat_bonuses ?? [],
            'effect_payload' => $this->effect_payload ?? [],
            'is_open' => (bool) $this->is_open,
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
