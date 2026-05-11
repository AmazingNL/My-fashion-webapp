<?php

declare(strict_types=1);

namespace App\DTO;

use JsonSerializable;

final class ProductListDto implements JsonSerializable
{
    public function __construct(
        public readonly string $title,
        public readonly array $products,
        public readonly int $totalCount,
        public readonly int $currentPage,
        public readonly int $pageSize,
        public readonly int $totalPages,
        public readonly int $startItem,
        public readonly int $endItem,
        public readonly bool $hasPreviousPage,
        public readonly bool $hasNextPage,
        public readonly int $previousPage,
        public readonly int $nextPage,
        public readonly array $filterCategories,
        public readonly array $currentFilters
    ) {
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}