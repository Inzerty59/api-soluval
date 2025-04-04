<?php

namespace App\Controller;

use App\Repository\PartRepository;
use App\Service\OpistoStockChecker;
use App\Service\Ovoko\DeletePartService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CallbackController
{
    private LoggerInterface $logger;
    private string $headerName;
    private string $headerValue;
    private PartRepository $partRepository;
    private OpistoStockChecker $opistoStockChecker;
    private DeletePartService $deletePartService;

    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params,
        PartRepository $partRepository,
        OpistoStockChecker $opistoStockChecker,
        DeletePartService $deletePartService
    ) {
        $this->logger = $logger;
        $this->headerName = $params->get('CALLBACK_HEADER_NAME');
        $this->headerValue = $params->get('CALLBACK_HEADER_VALUE');
        $this->partRepository = $partRepository;
        $this->opistoStockChecker = $opistoStockChecker;
        $this->deletePartService = $deletePartService;
    }

    /**
     * @Route("/api/callback", name="api_callback", methods={"POST"})
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $this->logger->info('handleCallback called');

        $authHeader = $request->headers->get($this->headerName);

        if ($authHeader !== $this->headerValue) {
            $this->logger->warning('Requête non autorisée reçue.');
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['event_type'])) {
            $this->logger->error('Requête invalide reçue.', ['data' => $data]);
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        switch ($data['event_type']) {
            case 'order.created':
                $this->handleOrderCreated($data['event_data']);
                break;

            case 'order.cancelled':
                $this->handleOrderCancelled($data['event_data']);
                break;

            case 'order.return.created':
                $this->handleOrderReturnCreated($data['event_data']);
                break;

            case 'order.return.updated':
                $this->handleOrderReturnUpdated($data['event_data']);
                break;

            case 'part.status.changed':
                $this->handlePartStatusChanged($data['event_data']);
                break;

            default:
                $this->logger->info('Événement non pris en charge.', ['event_type' => $data['event_type']]);
                return new JsonResponse(['message' => 'Event type not handled'], 200);
        }

        return new JsonResponse(['message' => 'Callback handled successfully'], 200);
    }

    private function handleOrderCreated(array $eventData): void
    {
        $orderId = $eventData['order_id'] ?? null;

        if ($orderId) {
            $this->logger->info('Nouvelle commande créée.', ['order_id' => $orderId]);
            // TODO: Ajouter la logique pour traiter une nouvelle commande
        }
    }

    private function handleOrderCancelled(array $eventData): void
    {
        $orderId = $eventData['order_id'] ?? null;

        if ($orderId) {
            $this->logger->info('Commande annulée.', ['order_id' => $orderId]);
            // TODO: Ajouter la logique pour traiter une commande annulée
        }
    }

    private function handleOrderReturnCreated(array $eventData): void
    {
        $returnId = $eventData['return_id'] ?? null;
        $orderId = $eventData['order_id'] ?? null;

        if ($returnId && $orderId) {
            $this->logger->info('Retour de commande initié.', ['return_id' => $returnId, 'order_id' => $orderId]);
            // TODO: Ajouter la logique pour traiter un retour de commande
        }
    }

    private function handleOrderReturnUpdated(array $eventData): void
    {
        $returnId = $eventData['return_id'] ?? null;
        $orderId = $eventData['order_id'] ?? null;

        if ($returnId && $orderId) {
            $this->logger->info('Retour de commande mis à jour.', ['return_id' => $returnId, 'order_id' => $orderId]);
            // TODO: Ajouter la logique pour mettre à jour un retour de commande
        }
    }

    private function handlePartStatusChanged(array $eventData): void
    {
        $externalId = $eventData['external_id'] ?? null;
        $status = $eventData['status'] ?? null;
        $partId = $eventData['part_id'] ?? null;

        if (!$externalId || !$partId) {
            $this->logger->error('Les identifiants external_id ou part_id sont manquants dans les données de l\'événement.', [
                'event_data' => $eventData,
            ]);
            return;
        }

        $this->logger->info('Statut de la pièce mis à jour.', [
            'external_id' => $externalId,
            'part_id' => $partId,
            'status' => $status,
        ]);

        switch ($status) {
            case 'reserved':
                $this->logger->info('Pièce réservée.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                ]);
                $this->handleReservedStatus($externalId, $partId);
                break;

            case 'in_warehouse':
                $this->logger->info('Pièce disponible en stock.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                ]);
                $this->handleInWarehouseStatus($externalId);
                break;

            case 'sold':
                $this->logger->info('Pièce vendue.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                ]);
                $this->handleSoldStatus($externalId, $partId);
                break;

            case 'returned':
                $this->logger->info('Pièce retournée.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                ]);
                // TODO: Ajouter la logique pour traiter une pièce retournée
                break;

            case 'written_off':
                $this->logger->info('Pièce mise hors service.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                ]);
                // TODO: Ajouter la logique pour marquer la pièce comme inutilisable
                break;

            default:
                $this->logger->warning('Statut de pièce non pris en charge.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                    'status' => $status,
                ]);
                break;
        }
    }

    private function handleReservedStatus(string $externalId, string $partId): void
    {
        if ($this->partRepository->isAvailable($externalId)) {
            $this->partRepository->updateAvailability($externalId, 0);
            $this->logger->info('Pièce mise à jour en non disponible dans la base de données.', [
                'external_id' => $externalId,
                'part_id' => $partId,
            ]);

            try {
                $isAvailableInOpisto = $this->opistoStockChecker->checkStock((int) $externalId);

                if (!$isAvailableInOpisto) {
                    $this->logger->info('Pièce non disponible dans Opisto. Suppression dans l\'API Ovoko.', [
                        'external_id' => $externalId,
                        'part_id' => $partId,
                    ]);
                    $this->deletePartService->deletePart($partId);
                }
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la vérification de la disponibilité dans Opisto.', [
                    'external_id' => $externalId,
                    'part_id' => $partId,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            $this->logger->info('Pièce déjà non disponible. Suppression dans l\'API Ovoko.', [
                'external_id' => $externalId,
                'part_id' => $partId,
            ]);
            $this->deletePartService->deletePart($partId);
        }
    }

    private function handleInWarehouseStatus(string $externalId): void
    {
        if (!$this->partRepository->isAvailable($externalId)) {
            $this->partRepository->updateAvailability($externalId, 1);
            $this->logger->info('Pièce mise à jour en disponible dans la base de données.', ['external_id' => $externalId]);
        } else {
            $this->logger->info('La pièce est déjà disponible dans la base de données.', ['external_id' => $externalId]);
        }
    }

    private function handleSoldStatus(string $externalId, string $partId): void
    {
        if (!$this->partRepository->isAvailable($externalId)) {
            $this->logger->info('La pièce est déjà marquée comme non disponible dans la base de données.', [
                'external_id' => $externalId,
                'part_id' => $partId,
            ]);

        } else {
            $this->logger->warning('La pièce est marquée comme disponible alors qu\'elle est vendue. Mise à jour en non disponible.', [
                'external_id' => $externalId,
                'part_id' => $partId,
            ]);

            $this->partRepository->updateAvailability($externalId, 0);
        }
    }
}