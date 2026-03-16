<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BattlePrepareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source_type' => ['required', 'string', Rule::in(['stage', 'dungeon', 'challenge', 'scripture'])],
            'source_id' => ['required', 'string', 'max:100'],
            'difficulty_id' => [
                Rule::requiredIf(fn (): bool => in_array((string) $this->input('source_type'), ['stage', 'dungeon', 'challenge'], true)),
                'nullable',
                'string',
                'max:100',
            ],
            'world_level' => [
                Rule::requiredIf(fn (): bool => (string) $this->input('source_type') === 'scripture'),
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }
}
