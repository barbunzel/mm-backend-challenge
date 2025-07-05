<?php

namespace App\PriceSaver;

use App\Dto\PriceData;
use App\Entity\Price;
use Doctrine\ORM\EntityManagerInterface;

final class PriceSaver
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function save(PriceData $priceDto): void
    {
        $priceEntity = $this->entityManager->find(Price::class, $priceDto->productId);

        if (null === $priceEntity) {
            $priceEntity = new Price();
            $priceEntity->setProductId($priceDto->productId);
        }

        $priceEntity->setVendorName($priceDto->vendorName);
        $priceEntity->setPrice($priceDto->price);
        $priceEntity->setFetchedAt($priceDto->fetchedAt);

        $this->entityManager->persist($priceEntity);
        $this->entityManager->flush();
    }
}
