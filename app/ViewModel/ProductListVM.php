<?php

namespace App\ViewModel;

class ProductListVM
{
    private array $products;
    private int $totalCount;
    private int $currentPage;
    private int $pageSize;
    private int $totalPages;

    public function __construct(array $products = [], int $totalCount = 0, int $currentPage = 1, int $pageSize = 10)
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->totalPages = max(1, (int) ceil($totalCount / max(1, $pageSize)));
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getStartItem(): int
    {
        if ($this->totalCount === 0) {
            return 0;
        }

        return (($this->currentPage - 1) * $this->pageSize) + 1;
    }

    public function getEndItem(): int
    {
        return min($this->currentPage * $this->pageSize, $this->totalCount);
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    public function getNextPage(): int
    {
        return min($this->totalPages, $this->currentPage + 1);
    }

}