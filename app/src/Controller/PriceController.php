<?php

namespace App\Controller;

use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class PriceController extends AbstractController
{
    public function __construct(
        private PriceRepository $priceRepository,
    ) {}

    #[Route('/prices', name: 'get_all_prices', methods: ['GET'])]
    public function getAllPrices(): JsonResponse
    {
        $prices = $this->priceRepository->findAll();

        return $this->json($prices);
    }

    #[Route('/price/{id}', name: 'get_price_by_id', methods: ['GET'])]
    public function getPriceById(string $id): JsonResponse
    {
        $price = $this->priceRepository->find($id);

        if (!$price) {
            return $this->json(
                ['message' => 'Price not found for product ID: ' . $id],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json($price);
    }
}
