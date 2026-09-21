<?php

namespace App\Repository;

use App\Entity\GlobalPreOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GlobalPreOrder>
 */
class GlobalPreOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GlobalPreOrder::class);
    }

    public function existsByOrderId(string $orderId): bool
    {
        return $this->count(['orderId' => $orderId]) > 0;
    }
}
