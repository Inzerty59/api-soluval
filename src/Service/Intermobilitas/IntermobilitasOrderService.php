<?php

namespace App\Service\Intermobilitas;

use App\Service\AuthenticationService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IntermobilitasOrderService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private AuthenticationService $authService;

    private const CASSE_ID = 4672;
    private const ORIGIN = 2;
    private const SHIPPING_STATUS = 1;
    private const STATUS = 1;
    private const TO_SEND = true;
    private const IS_FREE_SHIPPING = true;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        AuthenticationService $authService
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->authService = $authService;
    }

    public function processOrder(array $orderData): void
    {
        $orderId = $orderData['id'] ?? 'unknown';

        try {
            $this->logger->info('[Intermobilitas] Début du traitement de la commande', [
                'order_id' => $orderId,
                'items_count' => count($orderData['items'] ?? []),
            ]);

            $clientId = $this->getOrCreateOpistoClient($orderData['customer'] ?? []);

            if (!$clientId) {
                $this->logger->error('[Intermobilitas] Impossible de récupérer ou créer le client Opisto', [
                    'order_id' => $orderId,
                    'customer' => $orderData['customer'] ?? [],
                ]);
                return;
            }

            $this->createOpistoOrder($orderData, $clientId);

            $this->logger->info('[Intermobilitas] Commande traitée avec succès', [
                'order_id' => $orderId,
                'client_id' => $clientId,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('[Intermobilitas] Erreur lors du traitement de la commande', [
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
            $this->logger->error('[Intermobilitas] Email client manquant', [
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

                $this->logger->info('[Intermobilitas] Client trouvé chez Opisto', [
                    'email' => $email,
                    'client_id' => $clientId,
                ]);

                return $clientId;
            }

            $this->logger->info('[Intermobilitas] Client non trouvé, création en cours', [
                'email' => $email,
            ]);

            $createResponse = $this->httpClient->request('POST', 'https://api.opisto.fr/v2.15/clients', [
                'headers' => [
                    'Token' => $token,
                ],
                'json' => [
                    'Email' => $email,
                    'Firstname' => $customerData['firstName'] ?? $customerData['company'] ?? 'N/A',
                    'Lastname' => $customerData['lastName'] ?? '',
                ],
            ]);

            $createContent = $createResponse->toArray();

            if (isset($createContent['ObjectCreated']) && $createContent['ObjectCreated'] === true) {
                $clientId = $createContent['ObjectIdCreated'] ?? null;

                $this->logger->info('[Intermobilitas] Client créé avec succès chez Opisto', [
                    'email' => $email,
                    'client_id' => $clientId,
                ]);

                return $clientId;
            }

            $this->logger->error('[Intermobilitas] Échec de la création du client chez Opisto', [
                'email' => $email,
                'response' => $createContent,
            ]);

            return null;

        } catch (\Exception $e) {
            $this->logger->error('[Intermobilitas] Erreur lors de la gestion du client Opisto', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function createOpistoOrder(array $orderData, int $clientId): void
    {
        try {
            $token = $this->authService->getValidToken();
            $orderId = $orderData['id'] ?? 'unknown';

            $opistoPayload = $this->transformOrderDataForOpisto($orderData, $clientId);

            $this->logger->info('[Intermobilitas] Création de la commande Opisto', [
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
                $this->logger->error('[Intermobilitas] Réponse Opisto invalide', [
                    'order_id' => $orderId,
                    'response' => $responseContent,
                ]);
                return;
            }

            $opistoOrderId = $responseContent;

            $this->logger->info('[Intermobilitas] Commande créée avec succès chez Opisto', [
                'intermobilitas_order_id' => $orderId,
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
                $this->logger->warning('[Intermobilitas] Payment.Id non trouvé dans la commande Opisto', [
                    'opisto_order_id' => $opistoOrderId,
                ]);
                return;
            }

            $paymentId = $orderDetailsContent['Payment']['Id'];

            $totalAmount = (float) ($orderData['total'] ?? 0);

            if ($totalAmount <= 0) {
                $this->logger->warning('[Intermobilitas] Montant total invalide ou nul', [
                    'order_id' => $orderId,
                    'total' => $totalAmount,
                ]);
                return;
            }

            $paymentUpdateUrl = "https://api.opisto.fr/v2.15/orders/{$opistoOrderId}/payments/{$paymentId}";

            $paymentPayload = [
                "Amount" => $totalAmount,
                "TypePayment" => 4,
            ];

            $this->logger->info('[Intermobilitas] Mise à jour du paiement', [
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

            $this->logger->info('[Intermobilitas] Paiement mis à jour avec succès', [
                'opisto_order_id' => $opistoOrderId,
                'payment_id' => $paymentId,
                'amount' => $totalAmount,
                'response' => $paymentResponse->getContent(false),
            ]);

        } catch (\Exception $e) {
            $this->logger->error('[Intermobilitas] Erreur lors de la création de la commande Opisto', [
                'order_id' => $orderData['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function transformOrderDataForOpisto(array $orderData, int $clientId): array
    {
        $customer = $orderData['customer'] ?? [];
        $orderId = $orderData['id'] ?? 'unknown';
        $reference = $orderData['reference'] ?? '';
        $source = $orderData['source'] ?? '';
        $items = $orderData['items'] ?? [];

        $billingAddress = [
            "City" => $customer['city'] ?? '',
            "Country" => [
                "Name" => $this->getCountryName($customer['country'] ?? 'FR'),
                "ISOCode" => $customer['country'] ?? 'FR',
            ],
            "Email" => $customer['email'] ?? '',
            "Firstname" => $customer['firstName'] ?? $customer['company'] ?? 'N/A',
            "Lastname" => $customer['lastName'] ?? '',
            "Phone1" => $customer['phone'] ?? '',
            "PostCode" => $customer['postalCode'] ?? '',
            "Street" => ($customer['street'] ?? '') . ' ' . ($customer['houseNo'] ?? ''),
            "StreetAdditionnal" => $customer['houseNoSuffix'] ?? '',
        ];

        $deliveryAddress = $billingAddress;

        if (!empty($orderData['shippingAddress'])) {
            $deliveryAddress = [
                "City" => $orderData['city'] ?? $billingAddress['City'],
                "Country" => [
                    "Name" => $this->getCountryName($orderData['country'] ?? $customer['country'] ?? 'FR'),
                    "ISOCode" => $orderData['country'] ?? $customer['country'] ?? 'FR',
                ],
                "Email" => $orderData['email'] ?? $billingAddress['Email'],
                "Firstname" => $billingAddress['Firstname'],
                "Lastname" => $billingAddress['Lastname'],
                "Phone1" => $orderData['phone'] ?? $billingAddress['Phone1'],
                "PostCode" => $orderData['postalCode'] ?? $billingAddress['PostCode'],
                "Street" => ($orderData['street'] ?? '') . ' ' . ($orderData['houseNo'] ?? ''),
                "StreetAdditionnal" => $orderData['houseNoSuffix'] ?? '',
            ];
        }

        $platformName = $this->getPlatformNameFromSource($source);
        $comment = "Commande {$platformName}\n\n";
        $comment .= "⚠️ Ne pas mettre en facture ⚠️\n\n";
        $comment .= "{$platformName} va vous fournir les instructions de livraison.\n";
        $comment .= "Veuillez récupérer le bon de transport et valider la commande depuis votre boutique {$platformName}.\n\n";
        $comment .= "Numéro de commande {$platformName} : #{$orderId}";

        $partsPayload = [];
        foreach ($items as $item) {
            $externalId = $item['id'] ?? null;
            if ($externalId) {
                $partsPayload[] = [
                    "Key" => $externalId,
                    "Value" => 0.00,
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
            'GP' => 'FRANCE (Guadeloupe)',
            'MQ' => 'FRANCE (Martinique)',
            'RE' => 'FRANCE (Ile de la Réunion)',
            'CE' => 'FRANCE (Corse)',
            'YT' => 'FRANCE (Mayotte)',
            'PF' => 'FRANCE (Polynésie Française)',
            'BL' => 'FRANCE (Saint-Barthélemy)',
            'MF' => 'FRANCE (Saint-Martin)',
            'PM' => 'FRANCE (Saint-Pierre-et-Miquelon)',
            'WF' => 'FRANCE (Wallis et Futuna)',
            'NL' => 'PAYS-BAS',
            'GF' => 'GUYANE FRANCAISE',
            'MC' => 'MONACO',
            'AT' => 'AUTRICHE',
            'NO' => 'NORVÈGE',
            'SE' => 'SUÈDE',
            'FI' => 'FINLANDE',
            'DK' => 'DANEMARK',
            'GR' => 'GRÈCE',
            'MA' => 'MAROC',
            'DZ' => 'ALGÉRIE',
            'PL' => 'POLOGNE',
            'LT' => 'LITUANIE',
            'AL' => 'ALBANIE',
            'AD' => 'ANDORRE',
            'AM' => 'ARMÉNIE',
            'AZ' => 'AZERBAÏDJAN',
            'BY' => 'BÉLARUS',
            'BA' => 'BOSNIE-HERZÉGOVINE',
            'BG' => 'BULGARIE',
            'HR' => 'CROATIE',
            'CY' => 'CHYPRE',
            'CZ' => 'RÉPUBLIQUE TCHÈQUE',
            'EE' => 'ESTONIE',
            'GE' => 'GÉORGIE',
            'HU' => 'HONGRIE',
            'IS' => 'ISLANDE',
            'IE' => 'IRLANDE',
            'KZ' => 'KAZAKHSTAN',
            'LV' => 'LETTONIE',
            'LI' => 'LIECHTENSTEIN',
            'MD' => 'MOLDAVIE',
            'ME' => 'MONTÉNÉGRO',
            'MK' => 'MACÉDOINE DU NORD',
            'RU' => 'RUSSIE',
            'SM' => 'SAINT-MARIN',
            'RS' => 'SERBIE',
            'SK' => 'SLOVAQUIE',
            'SI' => 'SLOVÉNIE',
            'TR' => 'TURQUIE',
            'UA' => 'UKRAINE',
            'GB' => 'ROYAUME-UNI',
            'VA' => 'VATICAN',
            'DEU' => 'ALLEMAGNE',
            'ESP' => 'ESPAGNE',
        ];

        return $countries[strtoupper($isoCode)] ?? 'INCONNU';
    }

    private function getPlatformNameFromSource(string $source): string
    {
        $platformMapping = [
            'AL000000PL' => 'Allegro.pl',
            'AP000000DK' => 'AutoParts24',
            'AM000000DE' => 'Autoteile-Markt',
            'AZ000000ES' => 'Azeler Recambios',
            'BP000000PT' => 'B-Parts',
            'EB000000US' => 'eBay motors',
            'EB000000AT' => 'eBay.at',
            'EB000000Bx' => 'eBay.be V',
            'EB000000BE' => 'eBay.be W',
            'EB000000CH' => 'eBay.ch',
            'EB000000UK' => 'eBay.co.uk',
            'EB000000DE' => 'eBay.de',
            'EB000000ES' => 'eBay.es',
            'EB000000FR' => 'eBay.fr',
            'EB000000IE' => 'eBay.ie',
            'EB000000IT' => 'eBay.it',
            'EB000000NL' => 'eBay.nl',
            'EB000000PL' => 'eBay.pl',
            'FC000000FR' => 'France Casse',
            'MC000000FR' => 'MotorsClub',
            'OP000000FR' => 'Opisto',
            'OV000000LT' => 'Ovoko.com',
            'RF000000ES' => 'RecambioFacil',
            'TH000000DE' => 'Teilehaber',
            'VU000000FR' => 'Valused',
        ];
        
        // Vérification spéciale pour TotalParts qui commence par TP000000
        if (strpos($source, 'TP000000') === 0) {
            return 'TotalParts';
        }
        
        return $platformMapping[$source] ?? 'INTERMOBILITAS';
    }
}
