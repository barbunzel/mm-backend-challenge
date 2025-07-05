<?php

namespace App\PriceFetcher\Strategy;

use App\Dto\PriceData;

interface PriceFetchingStrategyInterface
{
  public function getSourceUrl(): string;

  public function fetchAndParsePrices(): array;
}
