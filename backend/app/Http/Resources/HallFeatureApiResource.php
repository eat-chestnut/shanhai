<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HallFeatureApiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feature_id' => $this->feature_id,
            'feature_name' => $this->feature_name,
            'feature_type' => $this->feature_type,
            'unlock_condition' => $this->unlock_condition ?? [],
            'jump_target' => $this->jump_target ?? [],
            'unlock_level' => data_get($this->unlock_condition, 'level'),
            'jump_page' => data_get($this->jump_target, 'page'),
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
