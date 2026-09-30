<?php

namespace App\DTO;

use App\Enums\ProductStatus;

readonly class ProductDto
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ProductStatus $status,
    ) {}

    /**
     * @param  array{name: string, description?: ?string, status?: ?string}  $data
     */
    public static function from(array $data): static
    {
        return new static(
            $data['name'],
            $data['description'] ?? null,
            isset($data['status']) ? ProductStatus::from($data['status']) : ProductStatus::Active,
        );
    }

    /**
     * @return array{name: string, description: ?string, status: ProductStatus}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
