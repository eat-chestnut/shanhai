<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BattleSettleRequest extends FormRequest
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
            'battle_id' => ['required', 'string', 'max:100'],
            'result' => ['required', 'string', 'max:30'],
            'duration' => ['required', 'numeric', 'min:0'],
            'cleared_wave' => ['required', 'integer', 'min:0'],
            'client_summary' => ['nullable', 'array'],
        ];
    }
}
