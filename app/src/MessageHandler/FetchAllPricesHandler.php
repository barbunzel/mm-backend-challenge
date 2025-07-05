<?php

namespace App\MessageHandler;

use App\Message\FetchAllPrices;
use App\Message\FindLowestPrice;
use App\PriceFetcher\PriceFetcherLocator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class FetchAllPricesHandler
{
    public function __construct(
        private PriceFetcherLocator $locator,
        private MessageBusInterface $bus,
        private LoggerInterface $appLogger,
    ) {}

    public function __invoke(FetchAllPrices $message): void
    {
        $this->appLogger->info('Starting price fetching batch...');

        $allPrices = [];
        $strategies = $this->locator->getAllStrategies();

        try {
            foreach ($strategies as $strategy) {
                $results = $strategy->fetchAndParsePrices();
                $allPrices = array_merge($allPrices, $results);
            }

            if (empty($allPrices)) {
                $this->appLogger->warning('No price were fetched from any source.');

                return;
            }

            $this->appLogger->info('Finished fetching. Dispatching to processor.', [
                'total_prices' => count($allPrices),
            ]);

            $this->bus->dispatch(new FindLowestPrice($allPrices));
        } catch (\Throwable $error) {
            $this->appLogger->critical('Critical error during price fetching, stopping batch.', [
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString(),
            ]);
        }
    }
}
