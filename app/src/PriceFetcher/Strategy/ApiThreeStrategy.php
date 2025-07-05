<?php

namespace App\PriceFetcher\Strategy;

use App\Dto\PriceData;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiThreeStrategy implements PriceFetchingStrategyInterface
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
        $data = $rawPriceData['data'];

        $fetchedAt = new \DateTimeImmutable();

        return array_reduce(array_keys($data), function(array $dataPerProduct, string $productId) use ($data, $fetchedAt) {
            $distributors = $data[$productId]['supplyChain']['distributors'] ?? [];

            $pricePerVendor = array_reduce($distributors, function (array $dataPerDistributor, array $distributor) use ($productId, $fetchedAt) {
                $offers = $distributor['offers'] ?? [];

                $pricesPerDistributor = array_map(function ($offer) use ($productId, $distributor, $fetchedAt) {
                    return new PriceData(
                        productId: $productId,
                        price: (float) $offer['unitCost'],
                        vendorName: $distributor['distributorName'],
                        fetchedAt: $fetchedAt,
                    );
                }, $offers);

                return array_merge($dataPerDistributor, $pricesPerDistributor);
            }, []);

            return array_merge($dataPerProduct, $pricePerVendor);
        }, []);
    }
}
