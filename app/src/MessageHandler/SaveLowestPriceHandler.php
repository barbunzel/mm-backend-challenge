<?php

namespace App\MessageHandler;

use App\Message\SaveLowestPrice;
use App\PriceSaver\PriceSaver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SaveLowestPriceHandler
{
    public function __construct(
        private PriceSaver $saver,
        private LoggerInterface $appLogger,
    ) {}
    
    public function __invoke(SaveLowestPrice $message): void
    {
        $priceDto = $message->getPriceDto();

        $this->saver->save($priceDto);

        $this->appLogger->info('Price for product {productId} saved to the database.', [
            'productId' => $priceDto->productId,
        ]);
    }
}
