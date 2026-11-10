<?php

namespace App\Service\Appropo;

use App\Entity\AppropoOrder;
use App\Repository\AppropoOrderRepository;
use App\Service\AuthenticationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppropoOrderService
{
    private const CASSE_ID = 4672;
    private const ORIGIN = 2;
    private const PLATFORM_NAME = 'Appropo';
    private const OPISTO_API = 'https://api.opisto.fr/v2.15';
    private const COUNTRIES = [
        'FR' => 'FRANCE', 'BE' => 'BELGIQUE', 'ES' => 'ESPAGNE', 'IT' => 'ITALIE',
        'LU' => 'LUXEMBOURG', 'PT' => 'PORTUGAL', 'CH' => 'SUISSE', 'DE' => 'ALLEMAGNE',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private AuthenticationService $authService,
        private AppropoOrderRepository $appropoOrderRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Crée client + commande Opisto pour une commande Appropo (pre_id = Opisto Part Key = part.external_id).
     * Idempotent sur order.id ; lève une exception si Opisto échoue pour qu'Appropo réessaie.
     */
    public function processOrder(array $orderData): void
    {
        $orderId = $orderData['order']['id'] ?? null;
        if (!$orderId) {
            $this->logger->error('[Appropo] order.id manquant, commande ignorée', ['payload' => $orderData]);
            return;
        }

        if ($this->appropoOrderRepository->existsByOrderId($orderId)) {
            $this->logger->info('[Appropo] Commande déjà traitée (idempotence)', ['order_id' => $orderId]);
            return;
        }

        $clientId = $this->getOrCreateOpistoClient($orderData['customer'] ?? []);
        if (!$clientId) {
            throw new \RuntimeException("Client Opisto introuvable/non créé pour la commande $orderId");
        }

        $opistoOrderId = $this->createOpistoOrder($orderData, $clientId);

        $this->entityManager->persist(new AppropoOrder($orderId, $opistoOrderId));
        $this->entityManager->flush();

        $this->logger->info('[Appropo] Commande traitée', [
            'order_id' => $orderId,
            'client_id' => $clientId,
            'opisto_order_id' => $opistoOrderId,
        ]);
    }

    private function getOrCreateOpistoClient(array $customer): ?int
    {
        $email = $customer['email'] ?? null;
        if (!$email) {
            $this->logger->error('[Appropo] Email client manquant', ['customer' => $customer]);
            return null;
        }

        $headers = ['Token' => $this->authService->getValidToken()];

        $found = json_decode($this->httpClient->request('GET', self::OPISTO_API . '/clients?email=' . urlencode($email), [
            'headers' => $headers,
        ])->getContent(false), true);

        if (!empty($found['Clients'][0]['Id'])) {
            return $found['Clients'][0]['Id'];
        }

        $created = $this->httpClient->request('POST', self::OPISTO_API . '/clients', [
            'headers' => $headers,
            'json' => [
                'Email' => $email,
                'Firstname' => $customer['firstname'] ?? $customer['company'] ?? 'N/A',
                'Lastname' => $customer['lastname'] ?? '',
            ],
        ])->toArray();

        return !empty($created['ObjectCreated']) ? ($created['ObjectIdCreated'] ?? null) : null;
    }

    private function createOpistoOrder(array $orderData, int $clientId): int
    {
        $orderId = $orderData['order']['id'];
        $headers = ['Token' => $this->authService->getValidToken()];

        $response = $this->httpClient->request('POST', self::OPISTO_API . '/orders', [
            'headers' => $headers,
            'json' => $this->buildOpistoPayload($orderData, $clientId),
        ])->getContent(false);

        // Opisto renvoie -1 si une pièce (Parts[].Key) est inconnue
        if (!is_numeric($response) || (int) $response <= 0) {
            throw new \RuntimeException("Commande $orderId refusée par Opisto (pièce inconnue ?) : $response");
        }
        $opistoOrderId = (int) $response;

        // Paiement : non bloquant, la commande existe déjà côté Opisto
        $amount = (float) ($orderData['order']['total_amount_ttc'] ?? 0);
        try {
            $details = $this->httpClient->request('GET', self::OPISTO_API . "/orders/$opistoOrderId", ['headers' => $headers])->toArray();
            $paymentId = $details['Payment']['Id'] ?? null;
            if ($paymentId && $amount > 0) {
                $this->httpClient->request('PUT', self::OPISTO_API . "/orders/$opistoOrderId/payments/$paymentId", [
                    'headers' => $headers,
                    'json' => ['Amount' => $amount, 'TypePayment' => 0],
                ])->getContent(false);
            } else {
                $this->logger->warning('[Appropo] Paiement non mis à jour', ['opisto_order_id' => $opistoOrderId, 'payment_id' => $paymentId, 'amount' => $amount]);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('[Appropo] Erreur mise à jour paiement', ['opisto_order_id' => $opistoOrderId, 'error' => $e->getMessage()]);
        }

        return $opistoOrderId;
    }

    private function buildOpistoPayload(array $orderData, int $clientId): array
    {
        $customer = $orderData['customer'] ?? [];
        $billing = $orderData['billing_address'] ?? [];
        $shipping = $orderData['shipping_address'] ?? $billing;
        $orderId = $orderData['order']['id'];

        $address = fn (array $a) => [
            'City' => $a['city'] ?? '',
            'Country' => [
                'Name' => self::COUNTRIES[strtoupper($a['country'] ?? 'FR')] ?? 'INCONNU',
                'ISOCode' => $a['country'] ?? 'FR',
            ],
            'Email' => $customer['email'] ?? '',
            'Firstname' => $customer['firstname'] ?? $customer['company'] ?? 'N/A',
            'Lastname' => $customer['lastname'] ?? '',
            'Phone1' => $customer['phone'] ?? '',
            'PostCode' => $a['zip_code'] ?? '',
            'Street' => trim(($a['address'] ?? '') . ' ' . ($a['address2'] ?? '')),
            'StreetAdditionnal' => '',
        ];

        $p = self::PLATFORM_NAME;
        $comment = "Commande $p\n\n⚠️ Ne pas mettre en facture ⚠️\n\n"
            . "$p va vous fournir les instructions de livraison.\n"
            . "Veuillez récupérer le bon de transport et valider la commande depuis votre boutique $p.\n\n"
            . "Numéro de commande $p : #$orderId";
        foreach (['delivery_note_url' => 'Bon de livraison', 'purchase_order_url' => 'Bon de commande'] as $key => $label) {
            if (!empty($orderData['order'][$key])) {
                $comment .= "\n$label : " . $orderData['order'][$key];
            }
        }

        $parts = [];
        foreach ($orderData['items'] ?? [] as $item) {
            if (!empty($item['pre_id'])) {
                $parts[] = ['Key' => $item['pre_id'], 'Value' => (float) ($item['discount_amount_ttc'] ?? 0)];
            }
        }

        return [
            'BillingAddress' => $address($billing),
            'DeliveryAddress' => $address($shipping),
            'CasseId' => self::CASSE_ID,
            'ClientId' => $clientId,
            'Comment' => $comment,
            'Origin' => self::ORIGIN,
            'ShippingStatus' => 1,
            'Status' => 1,
            'ToSend' => true,
            'IsFreeShipping' => true,
            'Parts' => $parts,
        ];
    }
}
