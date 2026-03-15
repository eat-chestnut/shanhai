<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHallFeatureRequest extends FormRequest
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
        return [
            'feature_id' => ['required', 'string', 'max:100', Rule::unique('hall_features', 'feature_id')],
            'feature_name' => ['required', 'string', 'max:100'],
            'feature_type' => ['required', 'string', 'max:100'],
            'unlock_condition' => ['required', 'array'],
            'unlock_condition.level' => ['required', 'integer', 'min:1'],
            'unlock_condition.conditions' => ['nullable', 'array'],
            'jump_target' => ['required', 'array'],
            'jump_target.page' => ['required', 'string', 'max:100'],
            'jump_target.params' => ['nullable', 'array'],
        ];
    }
}
