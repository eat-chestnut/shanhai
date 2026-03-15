<?php

namespace App\Http\Requests;

use App\Enums\RoleType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCharacterClassRequest extends FormRequest
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
        $characterClass = $this->route('characterClass');

        return [
            'class_id' => [
                'required',
                'string',
                'max:100',
                Rule::unique('character_classes', 'class_id')->ignore($characterClass?->id),
            ],
            'class_name' => ['required', 'string', 'max:100'],
            'class_desc' => ['nullable', 'string'],
            'role_type' => ['required', 'string', Rule::in(RoleType::values())],
            'is_open' => ['required', 'boolean'],
        ];
    }
}
