<?php

namespace App\Service\Ovoko;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class OrderPaymentService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->params = $params;
    }

    /**
     * Gère le processus de synchronisation, vérification et mise à jour du paiement.
     *
     * @param string $orderId
     * @param float $amount
     * @return void
     */
    public function handleOrderPayment(string $orderId, float $amount): void
    {
        try {
            $this->logger->info("Synchronisation de la commande {$orderId}...");
            $this->syncOrderToOpisto($orderId);

            $opistoOrderId = $this->getOpistoOrderId($orderId);

            if (!$opistoOrderId) {
                $this->logger->error("Impossible de récupérer l'ID de la commande Opisto pour {$orderId}.");
                return;
            }

            $paymentId = $this->getPaymentId($opistoOrderId);

            if (!$paymentId) {
                $this->logger->error("Impossible de récupérer l'ID du paiement pour la commande {$opistoOrderId}.");
                return;
            }

            $this->updatePayment($opistoOrderId, $paymentId, $amount);

            $this->logger->info("Paiement mis à jour avec succès pour la commande {$opistoOrderId}.");
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du paiement.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function syncOrderToOpisto(string $orderId): void
    {
    }

    private function getOpistoOrderId(string $orderId): ?string
    {
        $url = $this->params->get('AUTH_URL_API') . "/orders/{$orderId}";

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            return $data['Id'] ?? null;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération de l\'ID de la commande.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function getPaymentId(string $opistoOrderId): ?string
    {
        $url = $this->params->get('AUTH_URL_API') . "/orders/{$opistoOrderId}";

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            return $data['Payment']['Id'] ?? null;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération de l\'ID du paiement.', [
                'order_id' => $opistoOrderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function updatePayment(string $opistoOrderId, string $paymentId, float $amount): void
    {
        $url = $this->params->get('AUTH_URL_API') . "/orders/{$opistoOrderId}/payments/{$paymentId}";

        $payload = [
            "Amount" => $amount,
            "TypePayment" => 2,
        ];

        try {
            $this->httpClient->request('PUT', $url, [
                'json' => $payload,
            ]);

            $this->logger->info("Paiement mis à jour pour la commande {$opistoOrderId}.");
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du paiement.', [
                'order_id' => $opistoOrderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}