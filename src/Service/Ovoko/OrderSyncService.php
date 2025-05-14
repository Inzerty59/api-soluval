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

            $opistoOrderUrl = 'https://api-preprod.opisto.fr:8443/v2.15/orders';

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

                $orderDetailsUrl = "https://api-preprod.opisto.fr:8443/v2.15/orders/{$opistoOrderId}";
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

                    if (isset($orderDetails['total_price']['buyer']['amount'])) {
                        $amount = (float) $orderDetails['total_price']['buyer']['amount'];
                        $paymentUpdateUrl = "https://api-preprod.opisto.fr:8443/v2.15/orders/{$opistoOrderId}/payments/{$paymentId}";

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
            "Comment" => null,
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
     *
     * @param string $isoCode
     * @return string
     */
    private function getCountryName(string $isoCode): string
    {
        $countries = [
            'AL' => 'ALBANIA',
            'AD' => 'ANDORRA',
            'AM' => 'ARMENIA',
            'AT' => 'AUSTRIA',
            'AZ' => 'AZERBAIJAN',
            'BY' => 'BELARUS',
            'BE' => 'BELGIUM',
            'BA' => 'BOSNIA AND HERZEGOVINA',
            'BG' => 'BULGARIA',
            'HR' => 'CROATIA',
            'CY' => 'CYPRUS',
            'CZ' => 'CZECH REPUBLIC',
            'DK' => 'DENMARK',
            'EE' => 'ESTONIA',
            'FI' => 'FINLAND',
            'FR' => 'FRANCE',
            'GE' => 'GEORGIA',
            'DE' => 'GERMANY',
            'GR' => 'GREECE',
            'HU' => 'HUNGARY',
            'IS' => 'ICELAND',
            'IE' => 'IRELAND',
            'IT' => 'ITALY',
            'KZ' => 'KAZAKHSTAN',
            'LV' => 'LATVIA',
            'LI' => 'LIECHTENSTEIN',
            'LT' => 'LITHUANIA',
            'LU' => 'LUXEMBOURG',
            'MT' => 'MALTA',
            'MD' => 'MOLDOVA',
            'MC' => 'MONACO',
            'ME' => 'MONTENEGRO',
            'NL' => 'NETHERLANDS',
            'MK' => 'NORTH MACEDONIA',
            'NO' => 'NORWAY',
            'PL' => 'POLAND',
            'PT' => 'PORTUGAL',
            'RO' => 'ROMANIA',
            'RU' => 'RUSSIA',
            'SM' => 'SAN MARINO',
            'RS' => 'SERBIA',
            'SK' => 'SLOVAKIA',
            'SI' => 'SLOVENIA',
            'ES' => 'SPAIN',
            'SE' => 'SWEDEN',
            'CH' => 'SWITZERLAND',
            'TR' => 'TURKEY',
            'UA' => 'UKRAINE',
            'GB' => 'UNITED KINGDOM',
            'VA' => 'VATICAN CITY',
        ];

        return $countries[strtoupper($isoCode)] ?? 'UNKNOWN';
    }
}