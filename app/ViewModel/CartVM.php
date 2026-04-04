<?php

namespace App\ViewModel;

class CartVM
{
    private string $title;
    private array $cartItems;
    private float $total;
    private int $itemCount;
    private bool $isEmpty;
    private string $noticeMessage;
    private string $noticeType;

    public function __construct(
        string $title = 'Shopping Cart',
        array $cartItems = [],
        float $total = 0.0,
        int $itemCount = 0,
        bool $isEmpty = true,
        string $noticeMessage = '',
        string $noticeType = 'success'
    ) {
        $this->title = $title;
        $this->cartItems = $cartItems;
        $this->total = $total;
        $this->itemCount = $itemCount;
        $this->isEmpty = $isEmpty;
        $this->noticeMessage = $noticeMessage;
        $this->noticeType = $noticeType;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCartItems(): array
    {
        return $this->cartItems;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function getNoticeMessage(): string
    {
        return $this->noticeMessage;
    }

    public function getNoticeType(): string
    {
        return $this->noticeType;
    }
}
