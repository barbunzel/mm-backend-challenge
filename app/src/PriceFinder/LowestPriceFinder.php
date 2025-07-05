<?php

namespace App\PriceFinder;

use App\Dto\PriceData;

class LowestPriceFinder
{
    /**
     * @param PriceData[] $priceDtos
     * @return PriceData[] Array of lowest prices per productId
     */
    public function findLowestPricePerProduct(array $priceDtos): array
    {
        $pricesByProduct = array_reduce($priceDtos, function ($pricesByProduct, $priceDto) {
            $pricesByProduct[$priceDto->productId][] = $priceDto;

            return $pricesByProduct;
        }, []);

        $validPricesByProduct = array_filter($pricesByProduct);

        $lowestPrices = array_map([$this, 'findLowestPrice'], $validPricesByProduct);

        return $lowestPrices;
    }

    /**
     * @param PriceData[] $priceData
     */
    private function findLowestPrice(array $priceDtos): PriceData
    {
        return array_reduce($priceDtos, function ($lowestPriceDto, $priceDto) {
            if ($priceDto->price < $lowestPriceDto->price) {
                return $priceDto;
            }

            return $lowestPriceDto;
        }, $priceDtos[0]);
    }
}
