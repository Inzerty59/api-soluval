<?php

namespace App\Service\GlobalPre;

use App\Entity\GlobalPreOrder;
use App\Repository\GlobalPreOrderRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GlobalPreOrderService
{
    private const CASSE_ID = 4672;
    private const ORIGIN = 2;
    private const SHIPPING_STATUS = 1;
    private const STATUS = 1;
    private const TO_SEND = true;
    private const IS_FREE_SHIPPING = true;
    private const PLATFORM_NAME = 'Global PRE';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private AuthenticationService $authService,
        private GlobalPreOrderRepository $globalPreOrderRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Traite une commande reçue depuis Global PRE.
     * Réplique IntermobilitasOrderService::processOrder(), adapté au format
     * confirmé par ETAI (order/items/customer/billing_address/shipping_address).
     */
    public function processOrder(array $orderData): void
    {
        $orderId = $orderData['order']['id'] ?? null;

        if (!$orderId) {
            $this->logger->error('[GlobalPre] order.id manquant, commande ignorée', [
                'payload' => $orderData,
            ]);
            return;
        }

        if ($this->globalPreOrderRepository->existsByOrderId($orderId)) {
            $this->logger->info('[GlobalPre] Commande déjà traitée, ignorée (idempotence)', [
                'order_id' => $orderId,
            ]);
            return;
        }

        try {
            $this->logger->info('[GlobalPre] Début du traitement de la commande', [
                'order_id' => $orderId,
                'items_count' => count($orderData['items'] ?? []),
            ]);

            $clientId = $this->getOrCreateOpistoClient($orderData['customer'] ?? []);

            if (!$clientId) {
                $this->logger->error('[GlobalPre] Impossible de récupérer ou créer le client Opisto', [
                    'order_id' => $orderId,
                    'customer' => $orderData['customer'] ?? [],
                ]);
                return;
            }

            $opistoOrderId = $this->createOpistoOrder($orderData, $clientId);

            if ($opistoOrderId === null) {
                return;
            }

            $globalPreOrder = new GlobalPreOrder();
            $globalPreOrder->setOrderId($orderId);
            $globalPreOrder->setOpistoOrderId($opistoOrderId);
            $globalPreOrder->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($globalPreOrder);
            $this->entityManager->flush();

            $this->logger->info('[GlobalPre] Commande traitée avec succès', [
                'order_id' => $orderId,
                'client_id' => $clientId,
                'opisto_order_id' => $opistoOrderId,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('[GlobalPre] Erreur lors du traitement de la commande', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function getOrCreateOpistoClient(array $customerData): ?int
    {
        $email = $customerData['email'] ?? null;

        if (!$email) {
            $this->logger->error('[GlobalPre] Email client manquant', [
                'customer_data' => $customerData,
            ]);
            return null;
        }

        try {
            $token = $this->authService->getValidToken();

            $opistoApiUrl = "https://api.opisto.fr/v2.15/clients?email={$email}";
            $response = $this->httpClient->request('GET', $opistoApiUrl, [
                'headers' => [
                    'Token' => $token,
                ],
            ]);

            $responseContent = $response->getContent(false);
            $opistoClientData = json_decode($responseContent, true);

            if (isset($opistoClientData['Clients']) && count($opistoClientData['Clients']) > 0) {
                $clientId = $opistoClientData['Clients'][0]['Id'] ?? null;

                $this->logger->info('[GlobalPre] Client trouvé chez Opisto', [
                    'email' => $email,
                    'client_id' => $clientId,
                ]);

                return $clientId;
            }

            $this->logger->info('[GlobalPre] Client non trouvé, création en cours', [
                'email' => $email,
            ]);

            $createResponse = $this->httpClient->request('POST', 'https://api.opisto.fr/v2.15/clients', [
                'headers' => [
                    'Token' => $token,
                ],
                'json' => [
                    'Email' => $email,
                    'Firstname' => $customerData['firstname'] ?? $customerData['company'] ?? 'N/A',
                    'Lastname' => $customerData['lastname'] ?? '',
                ],
            ]);

            $createContent = $createResponse->toArray();

            if (isset($createContent['ObjectCreated']) && $createContent['ObjectCreated'] === true) {
                $clientId = $createContent['ObjectIdCreated'] ?? null;

                $this->logger->info('[GlobalPre] Client créé avec succès chez Opisto', [
                    'email' => $email,
                    'client_id' => $clientId,
                ]);

                return $clientId;
            }

            $this->logger->error('[GlobalPre] Échec de la création du client chez Opisto', [
                'email' => $email,
                'response' => $createContent,
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('[GlobalPre] Erreur lors de la gestion du client Opisto', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function createOpistoOrder(array $orderData, int $clientId): ?int
    {
        $orderId = $orderData['order']['id'] ?? 'unknown';

        try {
            $token = $this->authService->getValidToken();

            $opistoPayload = $this->transformOrderDataForOpisto($orderData, $clientId);

            $this->logger->info('[GlobalPre] Création de la commande Opisto', [
                'order_id' => $orderId,
                'payload' => $opistoPayload,
            ]);

            $response = $this->httpClient->request('POST', 'https://api.opisto.fr/v2.15/orders', [
                'headers' => [
                    'Token' => $token,
                ],
                'json' => $opistoPayload,
            ]);

            $responseContent = $response->getContent(false);

            if (!is_numeric($responseContent)) {
                $this->logger->error('[GlobalPre] Réponse Opisto invalide', [
                    'order_id' => $orderId,
                    'response' => $responseContent,
                ]);
                return null;
            }

            $opistoOrderId = (int) $responseContent;

            // Opisto renvoie -1 quand une des pièces (Parts[].Key) n'existe pas dans son catalogue
            if ($opistoOrderId <= 0) {
                $this->logger->error('[GlobalPre] Commande refusée par Opisto (pièce inconnue ?)', [
                    'order_id' => $orderId,
                    'opisto_response' => $opistoOrderId,
                    'dms_ids' => array_column($orderData['items'] ?? [], 'dms_id'),
                ]);
                return null;
            }

            $this->logger->info('[GlobalPre] Commande créée avec succès chez Opisto', [
                'globalpre_order_id' => $orderId,
                'opisto_order_id' => $opistoOrderId,
            ]);

            $orderDetailsUrl = "https://api.opisto.fr/v2.15/orders/{$opistoOrderId}";
            $orderDetailsResponse = $this->httpClient->request('GET', $orderDetailsUrl, [
                'headers' => [
                    'Token' => $token,
                ],
            ]);

            $orderDetailsContent = $orderDetailsResponse->toArray();

            if (!isset($orderDetailsContent['Payment']['Id'])) {
                $this->logger->warning('[GlobalPre] Payment.Id non trouvé dans la commande Opisto', [
                    'opisto_order_id' => $opistoOrderId,
                ]);
                return $opistoOrderId;
            }

            $paymentId = $orderDetailsContent['Payment']['Id'];
            $totalAmount = (float) ($orderData['order']['total_amount_ttc'] ?? 0);

            if ($totalAmount <= 0) {
                $this->logger->warning('[GlobalPre] Montant total invalide ou nul', [
                    'order_id' => $orderId,
                    'total' => $totalAmount,
                ]);
                return $opistoOrderId;
            }

            $paymentUpdateUrl = "https://api.opisto.fr/v2.15/orders/{$opistoOrderId}/payments/{$paymentId}";

            $paymentPayload = [
                "Amount" => $totalAmount,
                "TypePayment" => 0,
            ];

            $this->logger->info('[GlobalPre] Mise à jour du paiement', [
                'opisto_order_id' => $opistoOrderId,
                'payment_id' => $paymentId,
                'amount' => $totalAmount,
            ]);

            $paymentResponse = $this->httpClient->request('PUT', $paymentUpdateUrl, [
                'headers' => [
                    'Token' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentPayload,
            ]);

            $this->logger->info('[GlobalPre] Paiement mis à jour avec succès', [
                'opisto_order_id' => $opistoOrderId,
                'payment_id' => $paymentId,
                'amount' => $totalAmount,
                'response' => $paymentResponse->getContent(false),
            ]);

            return $opistoOrderId;

        } catch (\Exception $e) {
            $this->logger->error('[GlobalPre] Erreur lors de la création de la commande Opisto', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function transformOrderDataForOpisto(array $orderData, int $clientId): array
    {
        $customer = $orderData['customer'] ?? [];
        $orderId = $orderData['order']['id'] ?? 'unknown';
        $billing = $orderData['billing_address'] ?? [];
        $shipping = $orderData['shipping_address'] ?? $billing;
        $items = $orderData['items'] ?? [];

        $billingAddress = [
            "City" => $billing['city'] ?? '',
            "Country" => [
                "Name" => $this->getCountryName($billing['country'] ?? 'FR'),
                "ISOCode" => $billing['country'] ?? 'FR',
            ],
            "Email" => $customer['email'] ?? '',
            "Firstname" => $customer['firstname'] ?? $customer['company'] ?? 'N/A',
            "Lastname" => $customer['lastname'] ?? '',
            "Phone1" => $customer['phone'] ?? '',
            "PostCode" => $billing['zip_code'] ?? '',
            "Street" => trim(($billing['address'] ?? '') . ' ' . ($billing['address2'] ?? '')),
            "StreetAdditionnal" => '',
        ];

        $deliveryAddress = [
            "City" => $shipping['city'] ?? '',
            "Country" => [
                "Name" => $this->getCountryName($shipping['country'] ?? 'FR'),
                "ISOCode" => $shipping['country'] ?? 'FR',
            ],
            "Email" => $customer['email'] ?? '',
            "Firstname" => $customer['firstname'] ?? $customer['company'] ?? 'N/A',
            "Lastname" => $customer['lastname'] ?? '',
            "Phone1" => $customer['phone'] ?? '',
            "PostCode" => $shipping['zip_code'] ?? '',
            "Street" => trim(($shipping['address'] ?? '') . ' ' . ($shipping['address2'] ?? '')),
            "StreetAdditionnal" => '',
        ];

        $comment = "Commande " . self::PLATFORM_NAME . "\n\n";
        $comment .= "⚠️ Ne pas mettre en facture ⚠️\n\n";
        $comment .= self::PLATFORM_NAME . " va vous fournir les instructions de livraison.\n";
        $comment .= "Veuillez récupérer le bon de transport et valider la commande depuis votre boutique " . self::PLATFORM_NAME . ".\n\n";
        $comment .= "Numéro de commande " . self::PLATFORM_NAME . " : #{$orderId}";

        $partsPayload = [];
        foreach ($items as $item) {
            $dmsId = $item['dms_id'] ?? null;
            if ($dmsId) {
                $partsPayload[] = [
                    "Key" => $dmsId,
                    "Value" => (float) ($item['discount_amount_ttc'] ?? 0.00),
                ];
            }
        }

        return [
            "BillingAddress" => $billingAddress,
            "DeliveryAddress" => $deliveryAddress,
            "CasseId" => self::CASSE_ID,
            "ClientId" => $clientId,
            "Comment" => $comment,
            "Origin" => self::ORIGIN,
            "ShippingStatus" => self::SHIPPING_STATUS,
            "Status" => self::STATUS,
            "ToSend" => self::TO_SEND,
            "IsFreeShipping" => self::IS_FREE_SHIPPING,
            "Parts" => $partsPayload,
        ];
    }

    private function getCountryName(string $isoCode): string
    {
        $countries = [
            'FR' => 'FRANCE',
            'BE' => 'BELGIQUE',
            'ES' => 'ESPAGNE',
            'IT' => 'ITALIE',
            'LU' => 'LUXEMBOURG',
            'PT' => 'PORTUGAL',
            'CH' => 'SUISSE',
            'DE' => 'ALLEMAGNE',
        ];

        return $countries[strtoupper($isoCode)] ?? 'INCONNU';
    }
}
