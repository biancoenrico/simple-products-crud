<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductInput',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', default: 'active', enum: ProductStatus::class),
    ],
)]
class StoreProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(ProductStatus::class)],
        ];
    }
}
