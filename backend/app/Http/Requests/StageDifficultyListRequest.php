<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StageDifficultyListRequest extends FormRequest
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
            'node_id' => ['required', 'string', 'max:100'],
        ];
    }
}
