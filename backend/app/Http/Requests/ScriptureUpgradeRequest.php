<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScriptureUpgradeRequest extends FormRequest
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
            'scripture_id' => ['required', 'string', 'max:100'],
            'target_world_level' => ['required', 'integer', 'min:1'],
        ];
    }
}
