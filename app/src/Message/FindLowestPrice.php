<?php

namespace App\Message;

use App\Dto\PriceData;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async_priority_high')]
final class FindLowestPrice
{
    /** @var PriceData[] */
    private array $priceDtos;

    public function __construct(array $priceDtos)
    {
        $this->priceDtos = $priceDtos;
    }

    public function getPriceDtos(): array
    {
        return $this->priceDtos;
    }
}
