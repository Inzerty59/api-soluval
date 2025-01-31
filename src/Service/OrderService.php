<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\User;
use App\Entity\BillingAdress;
use App\Entity\DeliveryAdress;
use App\Entity\MangoPay;
use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\StockService;
use App\Service\CartService;

class OrderService
{
    private EntityManagerInterface $entityManager;
    private StockService $stockService;
    private CartService $cartService;

    public function __construct(EntityManagerInterface $entityManager, StockService $stockService, CartService $cartService)
    {
        $this->entityManager = $entityManager;
        $this->stockService = $stockService;
        $this->cartService = $cartService;
    }

    public function createOrder(User $user, MangoPay $mangoPay): Order
    {
        // Récupérer les adresses
        $billingAdress = $this->entityManager->getRepository(BillingAdress::class)->findOneBy(['user' => $user]);
        $deliveryAdress = $this->entityManager->getRepository(DeliveryAdress::class)->findOneBy(['user' => $user]);

        if (!$billingAdress || !$deliveryAdress) {
            throw new \Exception("Adresses de facturation et de livraison requises.");
        }

        // Récupérer le panier
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            throw new \Exception("Le panier est vide.");
        }

        // Récupérer les pièces en base
        $partIds = array_keys($cart);
        $parts = $this->entityManager->getRepository(Part::class)->findBy(['id' => $partIds]);

        if (empty($parts)) {
            throw new \Exception("Les pièces du panier ne sont plus disponibles.");
        }

        // Transaction pour sécuriser les opérations
        $this->entityManager->beginTransaction();

        try {
            // Création de la commande
            $order = new Order();
            $order->setUser($user);
            $order->setBillingAdress($billingAdress);
            $order->setDeliveryAdress($deliveryAdress);
            $order->setMangoPay($mangoPay);
            $order->setOrderNumber($this->generateOrderNumber());
            $order->setToSend(false);
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setUpdatedAt(new \DateTimeImmutable());

            // Vérification du stock et ajout des pièces
            foreach ($parts as $part) {
                $quantity = $cart[$part->getId()];
                if (!$this->stockService->isStockAvailable($part->getId(), $quantity)) {
                    throw new \Exception("Stock insuffisant pour : " . $part->getName());
                }

                // Décrémenter le stock proprement
                $this->stockService->decreaseStock($part, $quantity);
                $order->addPart($part);
            }

            // Sauvegarde
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            $this->entityManager->commit();

            // Vider le panier
            $this->cartService->clearCart();

            return $order;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \Exception("Erreur lors de la commande : " . $e->getMessage());
        }
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}
