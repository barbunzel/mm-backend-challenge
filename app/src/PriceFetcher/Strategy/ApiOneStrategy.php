<?php

namespace App\PriceFetcher\Strategy;

use App\Dto\PriceData;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiOneStrategy implements PriceFetchingStrategyInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiUrl,
    ) {}

    public function getSourceUrl(): string
    {
        return $this->apiUrl;
    }

    public function fetchAndParsePrices(): array
    {
        $rawPriceData = $this->fetchPrices();

        return $this->parsePrices($rawPriceData);
    }

    private function fetchPrices(): array
    {
        $response = $this->client->request('GET', $this->getSourceUrl());

        return $response->toArray();
    }

    private function parsePrices(array $rawPriceData): array
    {
        $validPriceData = array_filter($rawPriceData, function (array $priceData) {
            return isset($priceData['product_id']) && isset($priceData['prices']);
        });

        $fetchedAt = new \DateTimeImmutable();

        return array_reduce($validPriceData, function (array $dataPerVendor, array $priceData) use ($fetchedAt) {
            $pricePerVendor = array_map(function (array $price) use ($priceData, $fetchedAt) {
                return new PriceData(
                    productId: $priceData['product_id'],
                    price: (float) $price['price'],
                    vendorName: $price['vendor'],
                    fetchedAt: $fetchedAt,
                );
            }, $priceData['prices']);

            return array_merge($dataPerVendor, $pricePerVendor);
        }, []);
    }
}
