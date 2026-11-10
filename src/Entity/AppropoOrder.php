<?php

namespace App\Entity;

use App\Repository\AppropoOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppropoOrderRepository::class)]
class AppropoOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $orderId;

    #[ORM\Column(nullable: true)]
    private ?int $opistoOrderId = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $orderId, ?int $opistoOrderId)
    {
        $this->orderId = $orderId;
        $this->opistoOrderId = $opistoOrderId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getOpistoOrderId(): ?int
    {
        return $this->opistoOrderId;
    }
}
