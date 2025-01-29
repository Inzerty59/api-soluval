<?php

namespace App\Service;

use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Part;

class StockService
{
    private PartRepository $partRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(PartRepository $partRepository, EntityManagerInterface $entityManager)
    {
        $this->partRepository = $partRepository;
        $this->entityManager = $entityManager;
    }

    public function isStockAvailable(int $partId, int $quantity): bool
    {
        $part = $this->partRepository->find($partId);
        if (!$part) {
            throw new \Exception("Pièce introuvable.");
        }

        return $part->getStock() >= $quantity;
    }

    public function decreaseStock(Part $part, int $quantity): void
    {
        if ($part->getStock() < $quantity) {
            throw new \Exception("Stock insuffisant pour : " . $part->getName());
        }

        $part->setStock($part->getStock() - $quantity);
        $this->entityManager->persist($part);
        $this->entityManager->flush();
    }
}

