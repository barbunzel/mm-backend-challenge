<?php

namespace App\Message;

use App\Dto\PriceData;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async_priority_high')]
final class SaveLowestPrice
{
    public function __construct(private PriceData $priceDto)
    {
        $this->priceDto = $priceDto;
    }

    public function getPriceDto(): PriceData
    {
        return $this->priceDto;
    }
}
