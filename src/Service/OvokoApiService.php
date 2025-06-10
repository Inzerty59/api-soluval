<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OvokoApiService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    private string $username;
    private string $password;
    private string $userToken;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->username = $params->get('OVOKO_API_USERNAME');
        $this->password = $params->get('OVOKO_API_PASSWORD');
        $this->userToken = $params->get('OVOKO_API_USER_TOKEN');
    }

    public function markPartAsSold($externalId): void
    {
        $partId = $this->getOvokoPartId($externalId);
        if (!$partId) {
            $this->logger->error("Part Ovoko introuvable pour external_id $externalId");
            return;
        }

        $formData = [
            'username' => $this->username,
            'password' => $this->password,
            'user_token' => $this->userToken,
            'part_id' => $partId,
        ];

        $this->httpClient->request('POST', 'https://api.rrr.lt/crm/deletePart', [
            'body' => $formData,
        ]);
        $this->logger->info("Pièce Ovoko $partId supprimée via l'API.");
    }

    private function getOvokoPartId($externalId): ?string
    {
        $this->logger->info("Recherche du part_id Ovoko pour external_id $externalId");
        $formData = [
            'username' => $this->username,
            'password' => $this->password,
            'user_token' => $this->userToken,
        ];
        $url = 'https://api.rrr.lt/v2/get/parts?external_ids=' . urlencode($externalId);
        $response = $this->httpClient->request('POST', $url, [
            'body' => $formData,
        ]);
        $data = $response->toArray();
        return $data['data'][0]['id'] ?? null;
    }
}