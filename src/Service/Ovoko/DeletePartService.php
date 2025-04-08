<?php

namespace App\Service\Ovoko;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class DeletePartService
{
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->logger = $logger;
    }

    /**
     * Supprime une pièce via l'API Ovoko.
     *
     * @param string $ovokoPartId
     * @return bool
     */
    public function deletePart(string $ovokoPartId): bool
    {
        $url = $this->params->get('OVOKO_API_URL_DELETE_PART');

        $formData = [
            'username' => $this->params->get('OVOKO_API_USERNAME'),
            'password' => $this->params->get('OVOKO_API_PASSWORD'),
            'user_token' => $this->params->get('OVOKO_API_USER_TOKEN'),
            'part_id' => $ovokoPartId,
        ];

        $this->logger->info('Payload envoyé à l\'API Ovoko.', ['form_data' => $formData]);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => $formData, 
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = $response->toArray();

            if ($statusCode === 200 && $responseData['status_code'] === 'R200') {
                $this->logger->info('Pièce supprimée avec succès dans l\'API Ovoko.', ['part_id' => $ovokoPartId]);
                return true;
            }

            $this->logger->error('Erreur lors de la suppression de la pièce dans l\'API Ovoko.', [
                'part_id' => $ovokoPartId,
                'response' => $responseData,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Exception lors de la suppression de la pièce dans l\'API Ovoko.', [
                'part_id' => $ovokoPartId,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }
}