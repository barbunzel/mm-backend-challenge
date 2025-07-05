<?php

namespace App\Tests\PriceFetcher\Strategy;

use App\Dto\PriceData;
use App\PriceFetcher\Strategy\ApiOneStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiOneStrategyTest extends TestCase
{
    public function testFetchAndParsePricesCorrectlyParsesValidData(): void
    {
        // 1. Arrange
        $rawApiData = [
            [
                'product_id' => 'abc-123',
                'prices' => [
                    ['vendor' => 'Shop A', 'price' => 99.99],
                    ['vendor' => 'Shop B', 'price' => 105.50],
                ],
            ],
            ['product_id' => 'def-456', 'prices' => null], 
            ['some_other_key' => 'xyz-789'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($rawApiData);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $strategy = new ApiOneStrategy($client, 'http://fake-api-one.com');

        // 2. Act
        $results = $strategy->fetchAndParsePrices();

        // 3. Assert
        $this->assertCount(2, $results, 'Should only parse the 2 prices from the single valid product entry.');
        
        $this->assertInstanceOf(PriceData::class, $results[0]);
        $this->assertEquals('abc-123', $results[0]->productId);
        $this->assertEquals(99.99, $results[0]->price);
        $this->assertEquals('Shop A', $results[0]->vendorName);
    }
}
