<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' =>'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'inventory' => 'required|integer|min:0',
        ];
    }
}
