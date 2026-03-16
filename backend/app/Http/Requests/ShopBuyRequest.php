<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopBuyRequest extends FormRequest
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
            'shop_item_id' => ['required', 'string', 'max:100'],
            'count' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }
}
