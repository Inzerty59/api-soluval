<?php

namespace App\Controller\Order;

use App\Entity\Order;
use App\Entity\Part;
use App\Entity\User;
use App\Entity\BillingAdress;
use App\Entity\DeliveryAdress;
use App\Entity\MangoPay;
use App\Service\OrderService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderManagementController extends AbstractController
{
    private OrderService $orderService;
    private ManagerRegistry $doctrine;

    public function __construct(OrderService $orderService, ManagerRegistry $doctrine)
    {
        $this->orderService = $orderService;
        $this->doctrine = $doctrine;
    }

    
 //* Crée une commande avec vérification du stock et persistance en base.
#[Route('/order/create', name: 'order_create', methods: ['POST'])]
public function createOrder(Request $request): JsonResponse
{
    // Récupérer les données envoyées dans la requête POST
    $data = json_decode($request->getContent(), true);
    $userId = $data['user_id'] ?? null;
    $billingAdressId = $data['billing_address_id'] ?? null;
    $deliveryAdressId = $data['delivery_address_id'] ?? null;
    $mangoPayId = $data['mango_pay_id'] ?? null;
    $partIds = $data['part_ids'] ?? [];

    // Récupérer les entités de la base de données via ManagerRegistry
    $user = $this->doctrine->getRepository(User::class)->find($userId);
    $billingAdress = $this->doctrine->getRepository(BillingAdress::class)->find($billingAdressId);
    $deliveryAdress = $this->doctrine->getRepository(DeliveryAdress::class)->find($deliveryAdressId);
    $mangoPay = $this->doctrine->getRepository(MangoPay::class)->find($mangoPayId);
    $parts = $this->doctrine->getRepository(Part::class)->findBy(['id' => $partIds]);

    // Vérification des données
    if (!$user || !$billingAdress || !$deliveryAdress || !$mangoPay || empty($parts)) {
        return $this->json([
            'status' => 'error',
            'message' => 'Certaines données sont invalides ou manquantes.',
        ], 400);
    }

    try {
        // Créer la commande via le service OrderService
        $order = $this->orderService->createOrder($user, $billingAdress, $deliveryAdress, $mangoPay, $parts);

        // Retourner une réponse JSON avec l'ID de la commande
        return $this->json([
            'status' => 'success',
            'orderId' => $order->getId(),
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Affiche les détails d'une commande (GET).
     */
    #[Route('/order/{id<\d+>}', name: 'order_details', methods: ['GET'])]
    public function getOrderDetails(int $id): Response
    {
        // Récupérer la commande par son ID
        $order = $this->doctrine->getRepository(Order::class)->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        // Préparer les données à afficher
        $orderData = [
            'id' => $order->getId(),
            'user' => $order->getUser() ? $order->getUser()->getUsername() : null,
            'billingAddress' => $order->getBillingAdress() ? $order->getBillingAdress()->getFullAddress() : null,
            'deliveryAddress' => $order->getDeliveryAdress() ? $order->getDeliveryAdress()->getFullAddress() : null,
            'mangoPay' => $order->getMangoPay() ? $order->getMangoPay()->getTransactionId() : null,
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

        // Afficher la vue Twig avec les données de la commande
        return $this->render('order/details.html.twig', [
            'order' => $orderData,
        ]);
    }
}
