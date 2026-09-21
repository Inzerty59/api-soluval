<?php

namespace App\Entity;

use App\Repository\GlobalPreOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GlobalPreOrderRepository::class)]
class GlobalPreOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $orderId = null;

    #[ORM\Column(nullable: true)]
    private ?int $opistoOrderId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOpistoOrderId(): ?int
    {
        return $this->opistoOrderId;
    }

    public function setOpistoOrderId(?int $opistoOrderId): static
    {
        $this->opistoOrderId = $opistoOrderId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
