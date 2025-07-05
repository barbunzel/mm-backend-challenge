<?php

namespace App\Dto;

class PriceData
{
    public function __construct(
        public readonly string $productId,
        public readonly string $vendorName,
        public readonly float $price,
        public readonly \DateTimeImmutable $fetchedAt,
    ) {}
}
