<?php

namespace App\MessageHandler;

use App\Message\FindLowestPrice;
use App\Message\SaveLowestPrice;
use App\PriceFinder\LowestPriceFinder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class FindLowestPriceHandler
{
    public function __construct(
        private LowestPriceFinder $finder,
        private MessageBusInterface $bus,
        private LoggerInterface $appLogger,
    ) {}

    public function __invoke(FindLowestPrice $message): void
    {
        try {
            $allPrices = $message->getPriceDtos();

            $lowestPircesPerProduct = $this->finder->findLowestPricePerProduct($allPrices);

            $this->appLogger->info('Finished processing product prices.', [
                'total_products' => count($lowestPircesPerProduct),
            ]);

            foreach ($lowestPircesPerProduct as $lowestPrice) {
                $this->bus->dispatch(new SaveLowestPrice($lowestPrice));
            }
        } catch (\Throwable $error) {
            $this->appLogger->error('An unexpected error occurred while finding lowest prices.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);

            throw $error;
        }
    }
}
