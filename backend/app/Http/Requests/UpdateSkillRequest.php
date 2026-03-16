<?php

namespace App\Http\Requests;

use App\Enums\SkillEffectType;
use App\Enums\SkillTargetType;
use App\Enums\SkillType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $skill = $this->route('skill');

        return [
            'skill_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('skills', 'skill_id')->ignore($skill?->id),
            ],
            'class_id' => ['required', 'string', 'max:100', Rule::exists('character_classes', 'class_id')],
            'skill_name' => ['required', 'string', 'max:100'],
            'skill_desc' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(SkillType::values())],
            'effect_type' => ['nullable', 'string', Rule::in(SkillEffectType::values())],
            'target_type' => ['nullable', 'string', Rule::in(SkillTargetType::values())],
            'cooldown' => ['required', 'integer', 'min:0'],
            'cost' => ['required', 'integer', 'min:0'],
            'unlock_level' => ['required', 'integer', 'min:1'],
            'max_level' => ['required', 'integer', 'min:1'],
            'power_base' => ['required', 'integer', 'min:0'],
            'power_per_level' => ['required', 'integer', 'min:0'],
            'duration' => ['required', 'integer', 'min:0'],
            'chance' => ['nullable', 'numeric', 'between:0,1'],
            'stat_bonuses' => ['nullable', 'array'],
            'effect_payload' => ['nullable', 'array'],
            'is_open' => ['required', 'boolean'],
        ];
    }
}
