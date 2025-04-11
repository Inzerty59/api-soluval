<?php

namespace App\Service\Ovoko;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class OrderSyncService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;
    private array $exchangeRateCache = [];

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->params = $params;
    }

    /**
     * Synchronise une commande d'Ovoko vers Opisto.
     *
     * @param string $orderId
     * @return void
     */
    public function syncOrderToOpisto(string $orderId): void
    {
        try {
            $ovokoOrderUrl = $this->params->get('OVOKO_API_URL_ORDER') . "/{$orderId}";

            $ovokoResponse = $this->httpClient->request('GET', $ovokoOrderUrl);
            $ovokoData = $ovokoResponse->toArray();

            if (!isset($ovokoData['list'][0])) {
                $this->logger->error('Aucune commande trouvée sur Ovoko.', ['order_id' => $orderId]);
                return;
            }

            $orderData = $ovokoData['list'][0];

            $amount = (float) ($orderData['part_total_price']['buyer']['amount'] ?? 0);

            $opistoPayload = $this->transformOrderDataForOpisto($orderData);
            $opistoOrderUrl = $this->params->get('API_ORDER_URL');

            $opistoResponse = $this->httpClient->request('POST', $opistoOrderUrl, [
                'json' => $opistoPayload,
            ]);

            $opistoResponseData = $opistoResponse->toArray();
            $opistoOrderId = $opistoResponseData['id'] ?? null;

            $this->logger->info('Commande synchronisée avec succès vers Opisto.', [
                'order_id_ovoko' => $orderId,
                'order_id_opisto' => $opistoOrderId,
                'opisto_response' => $opistoResponse->getContent(),
            ]);

            if ($opistoOrderId) {
                $orderPaymentService = new OrderPaymentService($this->httpClient, $this->logger, $this->params);
                $orderPaymentService->handleOrderPayment($opistoOrderId, $amount);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la synchronisation de la commande.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Transforme les données de commande Ovoko pour correspondre au format Opisto.
     *
     * @param array $orderData
     * @return array
     */
    private function transformOrderDataForOpisto(array $orderData): array
    {
        $clientNameParts = explode(' ', $orderData['client_name'] ?? '');
        $firstName = $clientNameParts[0] ?? 'N/A';
        $lastName = isset($clientNameParts[1]) ? implode(' ', array_slice($clientNameParts, 1)) : 'N/A';

        return [
            "BillingAddress" => [
                "City" => $orderData['client_address_city'] ?? '',
                "Country" => [
                    "Name" => $this->getCountryName($orderData['client_address_country'] ?? 'FR'),
                    "ISOCode" => $orderData['client_address_country'] ?? 'FR',
                ],
                "Email" => $orderData['client_email'] ?? '',
                "Firstname" => $firstName,
                "Lastname" => $lastName,
                "Phone1" => $orderData['client_phone'] ?? '',
                "PostCode" => $orderData['client_address_zip_code'] ?? '',
                "Street" => $orderData['client_address_street'] ?? '',
                "StreetAdditionnal" => "",
            ],
            "DeliveryAddress" => [
                "City" => $orderData['client_address_city'] ?? '',
                "Country" => [
                    "Name" => $this->getCountryName($orderData['client_address_country'] ?? 'FR'),
                    "ISOCode" => $orderData['client_address_country'] ?? 'FR',
                ],
                "Email" => $orderData['client_email'] ?? '',
                "Firstname" => $firstName,
                "Lastname" => $lastName,
                "Phone1" => $orderData['client_phone'] ?? '',
                "PostCode" => $orderData['client_address_zip_code'] ?? '',
                "Street" => $orderData['client_address_street'] ?? '',
                "StreetAdditionnal" => "",
            ],
            "CasseId" => 4672,
            "ClientId" => 6842533,
            "Comment" => null,
            "Origin" => 2,
            "ShippingStatus" => 1,
            "Status" => 1,
            "ToSend" => true,
            "Parts" => array_map(function ($item) {
                $currency = $item['sell_price']['buyer']['currency'] ?? 'EUR';
                $amount = $item['sell_price']['buyer']['amount'] ?? 0;

                $exchangeRate = $this->getExchangeRate($currency);
                $priceInEuro = $exchangeRate > 0 ? $amount * $exchangeRate : 0;

                return [
                    "Key" => $item['external_id'],
                    "Value" => round($priceInEuro, 2),
                ];
            }, array_filter($orderData['item_list'] ?? [], function ($item) {
                return isset($item['external_id']);
            })),
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

    /**
     * Récupère le taux de change pour une devise donnée.
     *
     * @param string $currency
     * @return float
     */
    private function getExchangeRate(string $currency): float
    {
        $currency = strtoupper($currency);

        if ($currency === 'EUR') {
            return 1.0;
        }

        if (isset($this->exchangeRateCache[$currency])) {
            return $this->exchangeRateCache[$currency];
        }

        try {
            $exchangeRateApiUrl = $this->params->get('EXCHANGE_RATE_API_URL');

            $response = $this->httpClient->request('GET', $exchangeRateApiUrl);
            $data = $response->toArray();

            if (isset($data['conversion_rates'][$currency])) {
                $rate = $data['conversion_rates'][$currency];
                $this->exchangeRateCache[$currency] = $rate;
                return $rate;
            }

            $this->logger->error('Taux de change introuvable pour la devise.', ['currency' => $currency]);
            return 0.0;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des taux de change.', [
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
            return 0.0;
        }
    }
}