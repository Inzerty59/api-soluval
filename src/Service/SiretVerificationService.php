<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class SiretVerificationService
{
    private $httpClient;
    private $logger;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
    }

    public function verifySiret(string $siret): bool
    {
        $this->logger->info('Vérification du numéro SIRET: ' . $siret);

        $url = 'https://data.siren-api.fr/v3/etablissements/' . $siret;
        $this->logger->info('URL de la requête: ' . $url);

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'X-client-Secret' => $this->apiKey,
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $this->logger->info('API Insee response status code: ' . $statusCode);

        if ($statusCode !== 200) {
            $this->logger->error('Échec de la vérification du SIRET, code de statut: ' . $statusCode);
            return false;
        }

        $data = $response->toArray();
        $this->logger->info('Données reçues de l\'API Insee: ' . json_encode($data));

        $isValid = isset($data['etablissement']);
        $this->logger->info('Le numéro SIRET est ' . ($isValid ? 'valide' : 'invalide'));

        return $isValid;
    }
}