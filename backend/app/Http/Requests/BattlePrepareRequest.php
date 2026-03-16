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
            'source_type' => ['required', 'string', Rule::in(['stage', 'dungeon'])],
            'source_id' => ['required', 'string', 'max:100'],
            'difficulty_id' => ['required', 'string', 'max:100'],
        ];
    }
}
