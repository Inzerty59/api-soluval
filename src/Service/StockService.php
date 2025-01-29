<?php

namespace App\Service;

use App\Repository\PartRepository;

class StockService
{
    private $partRepository;

    public function __construct(PartRepository $partRepository)
    {
        $this->partRepository = $partRepository;
    }

    /**
     * Vérifie si une pièce a suffisamment de stock.
     *
     * @param int $partId
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function isStockAvailable(int $partId, int $quantity): bool
    {
        // Rechercher la pièce
        $part = $this->partRepository->find($partId);

        if (!$part) {
            throw new \Exception("Pièce introuvable.");
        }

        // Vérifier si le stock est suffisant
        return $part->getStock() >= $quantity;
    }
}
