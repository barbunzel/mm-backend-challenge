<?php

namespace App\Tests\PriceFinder;

use App\Dto\PriceData;
use App\PriceFinder\LowestPriceFinder;
use PHPUnit\Framework\TestCase;

class LowestPriceFinderTest extends TestCase
{
    private LowestPriceFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = new LowestPriceFinder();
    }

    public function testFindLowestPricePerProduct(): void
    {
        // 1. Arrange
        $fetchedAt = new \DateTimeImmutable();
        $priceDtos = [
            new PriceData('101', 'Vendor A', 150.00, $fetchedAt),
            new PriceData('101', 'Vendor B', 145.50, $fetchedAt),
            new PriceData('101', 'Vendor C', 155.25, $fetchedAt),

            new PriceData('202', 'Vendor X', 99.99, $fetchedAt),
            new PriceData('202', 'Vendor Y', 101.50, $fetchedAt),

            new PriceData('303', 'Vendor Z', 49.95, $fetchedAt),
        ];

        // 2. Act
        $lowestPrices = $this->finder->findLowestPricePerProduct($priceDtos);

        // 3. Assert
        $this->assertCount(3, $lowestPrices, 'Should return one lowest price for each unique product ID.');

        $this->assertArrayHasKey('101', $lowestPrices);
        $this->assertSame(145.50, $lowestPrices['101']->price);
        $this->assertSame('Vendor B', $lowestPrices['101']->vendorName);

        $this->assertArrayHasKey('202', $lowestPrices);
        $this->assertSame(99.99, $lowestPrices['202']->price);
        $this->assertSame('Vendor X', $lowestPrices['202']->vendorName);

        $this->assertArrayHasKey('303', $lowestPrices);
        $this->assertSame(49.95, $lowestPrices['303']->price);
        $this->assertSame('Vendor Z', $lowestPrices['303']->vendorName);
    }

    public function testFindLowestPriceWithIdenticalPrices(): void
    {
        // 1. Arrange
        $fetchedAt = new \DateTimeImmutable();
        $priceDtos = [
            new PriceData('404', 'Vendor A', 200.00, $fetchedAt),
            new PriceData('404', 'Vendor B', 199.00, $fetchedAt),
            new PriceData('404', 'Vendor C', 199.00, $fetchedAt),
        ];

        // 2. Act
        $lowestPrices = $this->finder->findLowestPricePerProduct($priceDtos);

        // 3. Assert
        $this->assertCount(1, $lowestPrices);
        $this->assertArrayHasKey('404', $lowestPrices);
        $this->assertSame(199.00, $lowestPrices['404']->price);
        $this->assertSame('Vendor B', $lowestPrices['404']->vendorName);
    }
}
