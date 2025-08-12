<?php

namespace App\Service\Ovoko;

use App\Service\AuthenticationService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class OrderSyncService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;
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
        ParameterBagInterface $params,
        AuthenticationService $authService
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->params = $params;
        $this->authService = $authService;
    }

    /**
     * Synchronise une commande d'Ovoko vers Opisto.
     *
     * @param array $orderDetails Les détails de la commande reçus d'Ovoko.
     * @param int $clientId L'ID du client récupéré ou créé chez Opisto.
     * @return void
     */
    public function syncOrderToOpisto(array $orderDetails, int $clientId): void
    {
        try {
            $opistoPayload = $this->transformOrderDataForOpisto($orderDetails, $clientId);

            $this->logger->info('Données transformées pour Opisto.', ['payload' => $opistoPayload]);

            $opistoOrderUrl = 'https://api.opisto.fr/v2.15/orders';

            $token = $this->authService->getValidToken();

            $response = $this->httpClient->request('POST', $opistoOrderUrl, [
                'headers' => [
                    'Token' => $token,
                ],
                'json' => $opistoPayload,
            ]);

            $responseContent = $response->getContent(false);
            $this->logger->info('Réponse brute de l\'API Opisto.', ['response' => $responseContent]);

            if (is_numeric($responseContent)) {
                $opistoOrderId = $responseContent;
                $this->logger->info('Commande envoyée avec succès à Opisto.', [
                    'order_id' => $orderDetails['order_id'],
                    'opisto_order_id' => $opistoOrderId,
                ]);

                $orderDetailsUrl = "https://api.opisto.fr/v2.15/orders/{$opistoOrderId}";
                $orderDetailsResponse = $this->httpClient->request('GET', $orderDetailsUrl, [
                    'headers' => [
                        'Token' => $token,
                    ],
                ]);

                $orderDetailsContent = $orderDetailsResponse->toArray();
                if (isset($orderDetailsContent['Payment']['Id'])) {
                    $paymentId = $orderDetailsContent['Payment']['Id'];
                    $this->logger->info('Payment.Id récupéré pour la commande Opisto.', [
                        'opisto_order_id' => $opistoOrderId,
                        'payment_id' => $paymentId,
                    ]);

                    if (isset($orderDetails['part_total_price']['seller']['amount'])) {
                        $amount = (float) $orderDetails['part_total_price']['seller']['amount'];
                        $paymentUpdateUrl = "https://api.opisto.fr/v2.15/orders/{$opistoOrderId}/payments/{$paymentId}";

                        $paymentPayload = [
                            "Amount" => $amount,
                            "TypePayment" => 10,
                        ];

                        try {
                            $paymentResponse = $this->httpClient->request('PUT', $paymentUpdateUrl, [
                                'headers' => [
                                    'Token' => $token,
                                    'Content-Type' => 'application/json',
                                ],
                                'json' => $paymentPayload,
                            ]);

                            $this->logger->info('Paiement mis à jour avec succès.', [
                                'opisto_order_id' => $opistoOrderId,
                                'payment_id' => $paymentId,
                                'response' => $paymentResponse->getContent(false),
                            ]);
                        } catch (\Exception $e) {
                            $this->logger->error('Erreur lors de la mise à jour du paiement.', [
                                'opisto_order_id' => $opistoOrderId,
                                'payment_id' => $paymentId,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        $this->logger->warning('Montant du paiement non trouvé dans les détails de la commande Ovoko.', [
                            'order_id' => $orderDetails['order_id'],
                        ]);
                    }
                } else {
                    $this->logger->warning('Payment.Id non trouvé dans les détails de la commande Opisto.', [
                        'opisto_order_id' => $opistoOrderId,
                    ]);
                }
            } else {
                $this->logger->error('Réponse inattendue de l\'API Opisto.', ['response' => $responseContent]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la synchronisation de la commande avec Opisto.', [
                'error' => $e->getMessage(),
                'order_details' => $orderDetails,
            ]);
        }
    }

    /**
     * Transforme les données de commande Ovoko pour correspondre au format Opisto.
     *
     * @param array $orderDetails Les détails de la commande reçus d'Ovoko.
     * @param int $clientId L'ID du client récupéré ou créé chez Opisto.
     * @return array Les données transformées au format attendu par Opisto.
     */
    private function transformOrderDataForOpisto(array $orderDetails, int $clientId): array
    {
        $clientNameParts = explode(' ', $orderDetails['client_name'] ?? '');
        $firstName = $clientNameParts[0] ?? 'N/A';
        $lastName = isset($clientNameParts[1]) ? implode(' ', array_slice($clientNameParts, 1)) : 'N/A';

        return [
            "BillingAddress" => [
                "City" => $orderDetails['client_address_city'] ?? '',
                "Country" => [
                    "Name" => $this->getCountryName($orderDetails['client_address_country'] ?? 'FR'),
                    "ISOCode" => $orderDetails['client_address_country'] ?? 'FR',
                ],
                "Email" => $orderDetails['client_email'] ?? '',
                "Firstname" => $firstName,
                "Lastname" => $lastName,
                "Phone1" => $orderDetails['client_phone'] ?? '',
                "PostCode" => $orderDetails['client_address_zip_code'] ?? '',
                "Street" => $orderDetails['client_address_street'] ?? '',
                "StreetAdditionnal" => "",
            ],
            "DeliveryAddress" => [
                "City" => $orderDetails['client_address_city'] ?? '',
                "Country" => [
                    "Name" => $this->getCountryName($orderDetails['client_address_country'] ?? 'FR'),
                    "ISOCode" => $orderDetails['client_address_country'] ?? 'FR',
                ],
                "Email" => $orderDetails['client_email'] ?? '',
                "Firstname" => $firstName,
                "Lastname" => $lastName,
                "Phone1" => $orderDetails['client_phone'] ?? '',
                "PostCode" => $orderDetails['client_address_zip_code'] ?? '',
                "Street" => $orderDetails['client_address_street'] ?? '',
                "StreetAdditionnal" => "",
            ],
            "CasseId" => self::CASSE_ID,
            "ClientId" => $clientId,
            "Comment" => "Commande OVOKO\n\n⚠️ Ne pas mettre en facture ⚠️\n\nOVOKO va vous fournir les instructions de livraison.\nVeuillez récupérer le bon de transport et valider la commande depuis votre boutique OVOKO.\n\nNuméro de commande OVOKO : #" . ($orderDetails['order_id'] ?? 'inconnu'),
            "Origin" => self::ORIGIN,
            "ShippingStatus" => self::SHIPPING_STATUS,
            "Status" => self::STATUS,
            "ToSend" => self::TO_SEND,
            "IsFreeShipping" => self::IS_FREE_SHIPPING,
            "Parts" => array_map(function ($item) {
                return [
                    "Key" => $item['external_id'],
                    "Value" => 0.00,
                ];
            }, $orderDetails['item_list'] ?? []),
        ];
    }

    /**
     * Transforme les données de commande France Casse pour correspondre au format Opisto.
     *
     * @param array $order Les détails de la commande reçus de France Casse.
     * @param int $clientId L'ID du client récupéré ou créé chez Opisto.
     * @return array Les données transformées au format attendu par Opisto.
     */
    public function transformFranceCasseOrder(array $order, int $clientId): array
    {
        return [
            "BillingAddress" => $order['BillingAddress'],
            "DeliveryAddress" => $order['DeliveryAddress'],
            "CasseId" => self::CASSE_ID,
            "ClientId" => $clientId,
            "Comment" => "Commande FRANCE CASSE\n\nNuméro de commande FRANCE CASSE : #" . ($order['Comment'] ?? 'inconnu'),
            "Origin" => self::ORIGIN,
            "ShippingStatus" => self::SHIPPING_STATUS,
            "Status" => self::STATUS,
            "ToSend" => self::TO_SEND,
            "IsFreeShipping" => self::IS_FREE_SHIPPING,
            "Parts" => array_map(function ($part) {
                return [
                    "Key" => $part['Key'],
                    "Value" => 0.00,
                ];
            }, $order['Parts'] ?? []),
        ];
    }

    /**
     *
     * @param string $isoCode
     * @return string
     */
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
            // autres pays européens si besoin
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
        ];

        return $countries[strtoupper($isoCode)] ?? 'INCONNU';
    }

    public function getAuthToken(): string
    {
        return $this->authService->getValidToken();
    }

    public function sendOrderToOpisto(array $payload, string $token): array
    {
        $url = 'https://api.opisto.fr/v2.15/orders';
        $this->logger->info('Payload envoyé à Opisto (France Casse).', [
            'url' => $url,
            'payload' => $payload,
        ]);
        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Token' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            $content = $response->getContent(false);
            $this->logger->info('Réponse brute de l\'API Opisto (France Casse).', [
                'response' => $content,
            ]);
            if ($content === '-200') {
                $this->logger->error('Erreur Opisto : pièce non disponible ou inexistante (France Casse).', [
                    'payload' => $payload,
                ]);
            }
            if (is_numeric($content)) {
                return ['order_id' => (int)$content];
            }
            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi de la commande France Casse à Opisto', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
            throw $e;
        }
    }

    public function updatePayment(int $orderId, int $paymentId, float $amount, string $token): void
    {
        $paymentUpdateUrl = "https://api.opisto.fr/v2.15/orders/{$orderId}/payments/{$paymentId}";
        $paymentPayload = [
            "Amount" => $amount,
            "TypePayment" => 10,
        ];

        try {
            $paymentResponse = $this->httpClient->request('PUT', $paymentUpdateUrl, [
                'headers' => [
                    'Token' => $token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentPayload,
            ]);

            $this->logger->info('Paiement mis à jour avec succès (France Casse).', [
                'opisto_order_id' => $orderId,
                'payment_id' => $paymentId,
                'response' => $paymentResponse->getContent(false),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du paiement (France Casse).', [
                'opisto_order_id' => $orderId,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getOrderDetails(int $orderId, string $token): array
    {
        $orderDetailsUrl = "https://api.opisto.fr/v2.15/orders/{$orderId}";
        try {
            $response = $this->httpClient->request('GET', $orderDetailsUrl, [
                'headers' => [
                    'Token' => $token,
                ],
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des détails de la commande Opisto.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}