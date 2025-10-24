<?php

namespace App\Service\Intermobilitas;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IntermobilitasApiService
{
    private const ACTION_INSERT = 'insert';
    private const ACTION_DELETE = 'delete';
    private const ACTION_GET_ALL = 'getAll';

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private ParameterBagInterface $params;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->params = $params;
    }

    public function insertOrUpdateItem(array $itemData): array
    {
        $time = (int) floor(microtime(true));
        $action = self::ACTION_INSERT;

        $payload = array_merge([
            'client' => $this->params->get('INTERMOBILITAS_CLIENT'),
            'time' => $time,
            'action' => $action,
            'auth' => $this->generateAuth($action, $time),
        ], $itemData);

        return $this->sendRequest($payload);
    }

    public function deleteItem(int $itemId): array
    {
        $time = (int) floor(microtime(true));
        $action = self::ACTION_DELETE;

        $payload = [
            'client' => $this->params->get('INTERMOBILITAS_CLIENT'),
            'time' => $time,
            'action' => $action,
            'auth' => $this->generateAuth($action, $time),
            'id' => $itemId,
        ];

        return $this->sendRequest($payload);
    }

    public function getAllItemIds(): array
    {
        $time = (int) floor(microtime(true));
        $action = self::ACTION_GET_ALL;

        $payload = [
            'client' => $this->params->get('INTERMOBILITAS_CLIENT'),
            'time' => $time,
            'action' => $action,
            'auth' => $this->generateAuth($action, $time),
        ];

        $response = $this->sendRequest($payload);
        return $response['data']['items'] ?? [];
    }

    private function generateAuth(string $action, int $time): string
    {
        $secret = $this->params->get('INTERMOBILITAS_SECRET');
        $raw = $time . $action . $secret;

        return hash('sha256', $raw);
    }

    private function sendRequest(array $payload): array
    {
        try {
            $this->logger->info('TotalParts API request', [
                'action' => $payload['action'],
                'id' => $payload['id'] ?? null,
                'payload' => $payload,
            ]);

            $response = $this->httpClient->request('POST', $this->params->get('INTERMOBILITAS_API_URL'), [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'auth_basic' => ['api-demo', '1235'],
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            $this->logger->info('TotalParts API response', [
                'status' => $statusCode,
                'result' => $data['result'] ?? null,
                'response' => $data,
            ]);

            if ($statusCode !== 200) {
                throw new \RuntimeException(sprintf('HTTP error %d: %s', $statusCode, json_encode($data)));
            }

            return $data;
        } catch (\Exception $e) {
            $this->logger->error('TotalParts API error', [
                'error' => $e->getMessage(),
                'action' => $payload['action'],
            ]);
            throw $e;
        }
    }
}
