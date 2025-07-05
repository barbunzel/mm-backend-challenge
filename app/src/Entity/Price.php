<?php

namespace App\Entity;

use App\Repository\PriceRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PriceRepository::class)]
class Price implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    #[Groups(['price:read'])]
    private ?string $productId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['price:read'])]
    private ?string $vendorName = null;

    #[ORM\Column]
    #[Groups(['price:read'])]
    private ?float $price = null;

    #[ORM\Column]
    #[Groups(['price:read'])]
    private ?\DateTimeImmutable $fetchedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    public function setVendorName(string $vendorName): static
    {
        $this->vendorName = $vendorName;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getFetchedAt(): ?\DateTimeImmutable
    {
        return $this->fetchedAt;
    }

    public function setFetchedAt(\DateTimeImmutable $fetchedAt): static
    {
        $this->fetchedAt = $fetchedAt;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'product_id' => $this->productId,
            'vendor_name' => $this->vendorName,
            'price' => $this->price,
            'fetched_at' => $this->fetchedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
