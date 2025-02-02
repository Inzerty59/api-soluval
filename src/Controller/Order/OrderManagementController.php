<?php

namespace App\Controller\Order;

use App\Entity\Order;
use App\Entity\Part;
use App\Entity\User;
use App\Entity\BillingAdress;
use App\Entity\DeliveryAdress;
use App\Entity\MangoPay;
use App\Service\CartService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderManagementController extends AbstractController
{
    private OrderService $orderService;
    private EntityManagerInterface $entityManager;
    private CartService $cartService;

    public function __construct(OrderService $orderService, EntityManagerInterface $entityManager, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
    }

    /**
     * Crée une commande avec vérification du stock et persistance en base.
     */
    #[Route('/order/create', name: 'order_create', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'Vous devez être connecté pour passer une commande.',
            ], 401);
        }

        // Récupérer les adresses directement depuis la base
        $billingAdress = $this->entityManager->getRepository(BillingAdress::class)->findOneBy(['user' => $user]);
        $deliveryAdress = $this->entityManager->getRepository(DeliveryAdress::class)->findOneBy(['user' => $user]);
        $mangoPay = $this->entityManager->getRepository(MangoPay::class)->findOneBy(['user' => $user]);

        if (!$billingAdress || !$deliveryAdress || !$mangoPay) {
            return $this->json([
                'status' => 'error',
                'message' => 'Adresses ou informations de paiement manquantes.',
            ], 400);
        }

        // Récupérer les pièces depuis la session via CartService
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Votre panier est vide.',
            ], 400);
        }

        // Récupérer les objets Part depuis la base
        $partIds = array_keys($cart);
        $parts = $this->entityManager->getRepository(Part::class)->findBy(['id' => $partIds]);

        if (empty($parts)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Les pièces sélectionnées ne sont plus disponibles.',
            ], 400);
        }

        // Vérifier le stock avant de valider la commande
        foreach ($parts as $part) {
            $quantity = $cart[$part->getId()] ?? 0;
            if ($part->getStock() < $quantity) {
                return $this->json([
                    'status' => 'error',
                    'message' => "Stock insuffisant pour la pièce : {$part->getName()}",
                ], 400);
            }
        }

        // Démarrer une transaction pour garantir la cohérence des données
        $this->entityManager->beginTransaction();

        try {
            // Créer la commande via OrderService
            $order = $this->orderService->createOrder($user, $billingAdress, $deliveryAdress, $mangoPay, $parts);

            // Décrémenter le stock
            foreach ($parts as $part) {
                $quantity = $cart[$part->getId()];
                $part->setStock($part->getStock() - $quantity);
                $this->entityManager->persist($part);
            }

            // Sauvegarder en base
            $this->entityManager->flush();
            $this->entityManager->commit();

            // Vider le panier
            $this->cartService->clearCart();

            return $this->json([
                'status' => 'success',
                'orderId' => $order->getId(),
            ]);
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return $this->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la création de la commande : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une commande.
     */
    #[Route('/order/{id<\d+>}', name: 'order_details', methods: ['GET'])]
    public function getOrderDetails(int $id): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $orderData = [
            'id' => $order->getId(),
            'user' => $order->getUser()?->getUsername(),
            'billingAddress' => $order->getBillingAdress()?->getFullAddress(),
            'deliveryAddress' => $order->getDeliveryAdress()?->getFullAddress(),
            'mangoPay' => $order->getMangoPay()?->getTransactionId(),
            'isFreeShipping' => $order->getIsFreeShipping(),
            'toSend' => $order->isToSend(),
            'createdAt' => $order->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'parts' => [],
        ];

        foreach ($order->getParts() as $part) {
            $orderData['parts'][] = [
                'id' => $part->getId(),
                'name' => $part->getName(),
                'stock' => $part->getStock(),
            ];
        }

        return $this->render('order/details.html.twig', [
            'order' => $orderData,
        ]);
    }
}
