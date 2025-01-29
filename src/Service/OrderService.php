<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Part;
use App\Entity\User;
use App\Entity\BillingAdress;
use App\Entity\DeliveryAdress;
use App\Entity\MangoPay;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private EntityManagerInterface $entityManager;
    private StockService $stockService;

    public function __construct(EntityManagerInterface $entityManager, StockService $stockService)
    {
        $this->entityManager = $entityManager;
        $this->stockService = $stockService;
    }

    public function createOrder(User $user, BillingAdress $billingAdress, DeliveryAdress $deliveryAdress, MangoPay $mangoPay, array $parts): Order
    {
        // Créer une nouvelle commande
        $order = new Order();
        $order->setUser($user);
        $order->setBillingAdress($billingAdress);
        $order->setDeliveryAdress($deliveryAdress);
        $order->setMangoPay($mangoPay);
        $order->setToSend(false);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUpdatedAt(new \DateTimeImmutable());

        // Vérification du stock et ajout des pièces à la commande
        foreach ($parts as $part) {
    if (!$this->stockService->isStockAvailable($part->getId(), 1)) {
        throw new \Exception(sprintf('Stock insuffisant pour la pièce %s', $part->getName()));
    }

    // Réduire le stock de la pièce
    $part->setStock($part->getStock() - 1);
    $order->addPart($part);
}


        // Persister et sauvegarder en base de données
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
