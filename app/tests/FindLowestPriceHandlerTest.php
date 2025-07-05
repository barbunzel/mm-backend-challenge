<?php

namespace App\Tests\MessageHandler;

use App\Dto\PriceData;
use App\Message\FindLowestPrice;
use App\Message\SaveLowestPrice;
use App\MessageHandler\FindLowestPriceHandler;
use App\PriceFinder\LowestPriceFinder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class FindLowestPriceHandlerTest extends TestCase
{
    private LowestPriceFinder $finderMock;
    private MessageBusInterface $busMock;
    private LoggerInterface $loggerMock;
    private FindLowestPriceHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->finderMock = $this->createMock(LowestPriceFinder::class);
        $this->busMock = $this->createMock(MessageBusInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->handler = new FindLowestPriceHandler(
            $this->finderMock,
            $this->busMock,
            $this->loggerMock
        );
    }

    public function testInvokeDispatchesSaveMessages(): void
    {
        // 1. Arrange
        $fetchedAt = new \DateTimeImmutable();

        $allPrices = [
            new PriceData('101', 'Vendor A', 150.00, $fetchedAt),
            new PriceData('101', 'Vendor B', 145.50, $fetchedAt),
            new PriceData('202', 'Vendor X', 99.99, $fetchedAt),
        ];
        $message = new FindLowestPrice($allPrices);

        $lowestPrices = [
            '101' => new PriceData('101', 'Vendor B', 145.50, $fetchedAt),
            '202' => new PriceData('202', 'Vendor X', 99.99, $fetchedAt),
        ];

        $this->finderMock
            ->expects($this->once())
            ->method('findLowestPricePerProduct')
            ->with($allPrices)
            ->willReturn($lowestPrices);

        $expectedDispatchedDtos = [
            $lowestPrices['101'],
            $lowestPrices['202'],
        ];
        $callCounter = 0;

        $this->busMock
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function (SaveLowestPrice $dispatchedMessage) use (&$callCounter, $expectedDispatchedDtos) {
                $this->assertSame($expectedDispatchedDtos[$callCounter], $dispatchedMessage->getPriceDto());
                $callCounter++;
                return true;
            }))
            ->willReturn(new Envelope($message));

        // 2. Act
        ($this->handler)($message);
    }
}
