<?php

namespace App\Repository;

use App\Entity\AppropoOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppropoOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppropoOrder::class);
    }

    public function existsByOrderId(string $orderId): bool
    {
        return $this->count(['orderId' => $orderId]) > 0;
    }
}
