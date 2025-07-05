<?php

namespace App\Tests\Integration;

use App\PriceFetcher\PriceFetcherLocator;
use App\PriceFetcher\Strategy\ApiOneStrategy;
use App\PriceFetcher\Strategy\ApiTwoStrategy;
use App\PriceFetcher\Strategy\ApiThreeStrategy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PriceFetcherLocatorTest extends KernelTestCase
{
    /**
     * This test verifies that the service container is correctly configured
     * to find all services tagged with 'app.price_fetching_strategy' and
     * inject them into the PriceFetcherLocator.
     */
    public function testStrategiesAreLoadedViaDependencyInjection(): void
    {
        // 1. Arrange
        self::bootKernel();
        $container = static::getContainer();

        // 2. Act
        $locator = $container->get(PriceFetcherLocator::class);
        $this->assertInstanceOf(PriceFetcherLocator::class, $locator);

        $strategies = $locator->getAllStrategies();

        // 3. Assert
        $this->assertIsIterable($strategies);

        $strategiesArray = iterator_to_array($strategies);

        $this->assertCount(3, $strategiesArray, 'Expected exactly 3 fetching strategies to be loaded.');

        $this->assertTrue($this->hasInstanceOf(ApiOneStrategy::class, $strategiesArray));
        $this->assertTrue($this->hasInstanceOf(ApiTwoStrategy::class, $strategiesArray));
        $this->assertTrue($this->hasInstanceOf(ApiThreeStrategy::class, $strategiesArray));
    }

    /**
     * Helper function to check if an array of objects contains an
     * instance of a specific class.
     *
     * @param string $class FQCN of the class to find
     * @param array $haystack The array of objects to search in
     * @return bool
     */
    private function hasInstanceOf(string $class, array $haystack): bool
    {
        foreach ($haystack as $object) {
            if ($object instanceof $class) {
                return true;
            }
        }
        return false;
    }
}
