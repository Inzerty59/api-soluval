<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiretVerificationService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function verifySiret(string $siret): bool
    {
        $response = $this->httpClient->request('GET', 'https://api.insee.fr/entreprises/sirene/V3/siret/' . $siret, [
            'headers' => [
                'Authorization' => 'Bearer YOUR_API_KEY', // créer compte sur https://api.insee.fr/ pour obtenir API KEY
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = $response->toArray();

        return isset($data['etablissement']);
    }
}