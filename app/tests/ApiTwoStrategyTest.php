<?php

namespace App\Tests\PriceFetcher\Strategy;

use App\Dto\PriceData;
use App\PriceFetcher\Strategy\ApiTwoStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiTwoStrategyTest extends TestCase
{
    public function testFetchAndParsePricesCorrectlyParsesValidData(): void
    {
        // 1. Arrange
        $rawApiData = [
            [
                'id' => 'prod-777',
                'competitor_data' => [
                    ['name' => 'Competitor X', 'amount' => 50.00],
                    ['name' => 'Competitor Y', 'amount' => 52.25],
                ],
            ],
            ['id' => 'prod-888', 'competitor_data' => null],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($rawApiData);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $strategy = new ApiTwoStrategy($client, 'http://fake-api-two.com');

        // 2. Act
        $results = $strategy->fetchAndParsePrices();

        // 3. Assert
        $this->assertCount(2, $results);

        $this->assertInstanceOf(PriceData::class, $results[1]);
        $this->assertEquals('prod-777', $results[1]->productId);
        $this->assertEquals(52.25, $results[1]->price);
        $this->assertEquals('Competitor Y', $results[1]->vendorName);
    }
}
