<?php

namespace App\PriceFetcher;

use App\PriceFetcher\Strategy\PriceFetchingStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class PriceFetcherLocator
{
    public function __construct(
        #[TaggedIterator('app.price_fetching_strategy')]
        private iterable $strategies
    ) {}

    public function getAllStrategies(): iterable
    {
        return $this->strategies;
    }
}
