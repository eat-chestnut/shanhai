<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EquipmentSocketGemRequest extends FormRequest
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
            'equipment_uid' => ['required', 'string', 'max:120'],
            'gem_id' => ['required', 'string', 'max:100'],
            'slot_index' => ['required', 'integer', 'min:0'],
        ];
    }
}
