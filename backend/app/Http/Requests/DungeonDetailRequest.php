<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DungeonDetailRequest extends FormRequest
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
            'dungeon_id' => ['required', 'string', 'max:100'],
        ];
    }
}
