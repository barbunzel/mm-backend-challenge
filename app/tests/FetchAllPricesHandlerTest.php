<?php

namespace App\Tests\MessageHandler;

use App\Dto\PriceData;
use App\Message\FetchAllPrices;
use App\Message\FindLowestPrice;
use App\MessageHandler\FetchAllPricesHandler;
use App\PriceFetcher\PriceFetcherLocator;
use App\PriceFetcher\Strategy\PriceFetchingStrategyInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class FetchAllPricesHandlerTest extends TestCase
{
    private PriceFetcherLocator $locator;
    private MessageBusInterface $bus;
    private LoggerInterface $logger;
    private FetchAllPricesHandler $handler;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(PriceFetcherLocator::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new FetchAllPricesHandler($this->locator, $this->bus, $this->logger);
    }

    public function testInvokeSuccessFetchesAndDispatches(): void
    {
        // 1. Arrange
        $strategy1 = $this->createMock(PriceFetchingStrategyInterface::class);
        $strategy1->method('fetchAndParsePrices')->willReturn([new PriceData('prod-1', 'Vendor A', 100, new \DateTimeImmutable())]);

        $strategy2 = $this->createMock(PriceFetchingStrategyInterface::class);
        $strategy2->method('fetchAndParsePrices')->willReturn([new PriceData('prod-2', 'Vendor B', 200, new \DateTimeImmutable())]);

        $this->locator->method('getAllStrategies')->willReturn([$strategy1, $strategy2]);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (FindLowestPrice $message) {
                return 2 === count($message->getPriceDtos());
            }))
            ->willReturn(new Envelope(new \stdClass()));

        // 2. Act
        ($this->handler)(new FetchAllPrices());
    }

    public function testInvokeStopsAndLogsOnStrategyFailure(): void
    {
        // 1. Arrange
        $failingStrategy = $this->createMock(PriceFetchingStrategyInterface::class);
        $failingStrategy->method('fetchAndParsePrices')
                        ->will($this->throwException(new \RuntimeException('API is down')));
        
        $workingStrategy = $this->createMock(PriceFetchingStrategyInterface::class);
        $workingStrategy->expects($this->never())->method('fetchAndParsePrices');

        $this->locator->method('getAllStrategies')->willReturn([$failingStrategy, $workingStrategy]);

        $this->bus->expects($this->never())->method('dispatch');

        $this->logger->expects($this->once())->method('critical');

        // 2. Act
        ($this->handler)(new FetchAllPrices());
    }
}
