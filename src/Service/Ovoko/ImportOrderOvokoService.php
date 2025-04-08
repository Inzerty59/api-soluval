<?php

namespace App\Service\Ovoko;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportOrderOvokoService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiUrl;
    private string $apiToken;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        string $ovokoApiUrl,
        string $ovokoApiToken
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiUrl = rtrim($ovokoApiUrl, '/');
        $this->apiToken = $ovokoApiToken;
    }

    /**
     * Récupère les commandes depuis l’API Ovoko.
     *
     * @return array
     * @throws \Exception
     */
    public function fetchOrders(): array
    {
        $url = $this->apiUrl . '/orders'; // endpoint à adapter si différent

        try {
            $response = $this->httpClient->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception("Erreur HTTP $statusCode lors de l'appel à Ovoko");
            }

            $data = $response->toArray();

            $this->logger->info('Commandes Ovoko récupérées avec succès', [
                'total' => count($data),
            ]);

            return $data;
        } catch (\Exception $e) {
            $this->logger->error('Erreur de récupération des commandes Ovoko', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Erreur de récupération des commandes Ovoko : " . $e->getMessage());
        }
    }
}
