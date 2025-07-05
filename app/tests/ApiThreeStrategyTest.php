<?php

namespace App\Tests\PriceFetcher\Strategy;

use App\Dto\PriceData;
use App\PriceFetcher\Strategy\ApiThreeStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiThreeStrategyTest extends TestCase
{
    public function testFetchAndParsePricesCorrectlyHandlesNestedData(): void
    {
        // 1. Arrange
        $rawApiData = [
            'data' => [
                'prod-alpha' => [
                    'supplyChain' => [
                        'distributors' => [
                            [
                                'distributorName' => 'Supplier One',
                                'offers' => [
                                    ['unitCost' => 120.50],
                                    ['unitCost' => 122.00],
                                ],
                            ],
                        ],
                    ],
                ],
                'prod-beta' => [
                    'supplyChain' => [
                        'distributors' => [
                            [
                                'distributorName' => 'Supplier Two',
                                'offers' => [['unitCost' => 300.00]],
                            ],
                        ],
                    ],
                ],
                'prod-gamma-no-offers' => [
                    'supplyChain' => [
                        'distributors' => [
                            ['distributorName' => 'Supplier Three', 'offers' => []],
                        ],
                    ],
                ],
                'prod-delta-no-distributors' => [
                    'supplyChain' => [],
                ],
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($rawApiData);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $strategy = new ApiThreeStrategy($client, 'http://fake-api-three.com');

        // 2. Act
        $results = $strategy->fetchAndParsePrices();

        // 3. Assert
        $this->assertCount(3, $results);

        $this->assertInstanceOf(PriceData::class, $results[0]);
        $this->assertEquals('prod-alpha', $results[0]->productId);
        $this->assertEquals(120.50, $results[0]->price);
        $this->assertEquals('Supplier One', $results[0]->vendorName);
        
        $this->assertEquals('prod-beta', $results[2]->productId);
        $this->assertEquals(300.00, $results[2]->price);
        $this->assertEquals('Supplier Two', $results[2]->vendorName);
    }
}
