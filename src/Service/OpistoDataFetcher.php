<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpistoDataFetcher
{
    private HttpClientInterface $client;
    private string $apiUrl;
    private AuthenticationService $authService;

    public function __construct(HttpClientInterface $client, AuthenticationService $authService, string $apiUrl)
    {
        $this->client = $client;
        $this->authService = $authService;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Récupère les pièces depuis l'API Opisto.
     *
     * @param array $filters Les filtres à appliquer à la requête (ex : ['brandName' => 'TOYOTA']).
     * @return array Les données des pièces récupérées depuis l'API.
     * @throws \Exception Si une erreur survient lors de la requête ou du traitement des données.
     */
    public function fetchParts(array $filters = []): array
    {
        // Récupérer un token valide
        $token = $this->authService->getValidToken();

        // Définir les paramètres par défaut
        $queryParams = array_merge([
            'itemsPerPage' => 40000,  // Nombre d'éléments par page
            'page' => 0,          // Numéro de la page
            'onlyParts' => "true",  // Limiter la recherche aux pièces
        ], $filters);

        try {
            // Appel API au point d'accès `/parts`
            $response = $this->client->request('GET', $this->apiUrl . '/parts', [
                'query' => $queryParams,
                'headers' => [
                    'Token' => $token,
                    'Accept' => 'application/json; charset=utf-8', 
                    'Content-Type' => 'application/json; charset=utf-8', 
                ],
            ]);

            // Récupérer le contenu brut pour inspection
            $content = $response->getContent(false); // Récupère le contenu sans lever d'exception
            $statusCode = $response->getStatusCode();

            // Vérification du statut HTTP
            if ($statusCode !== 200) {
                throw new \Exception("Erreur HTTP $statusCode : $content");
            }

            // Décodage du contenu JSON
            $data = json_decode($content, true);

            // Vérification des erreurs de décodage JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de décodage JSON : ' . json_last_error_msg());
            }

            return $data; // Retourne les données des pièces
        } catch (\Exception $e) {
            // Lève une exception avec un message détaillé
            throw new \Exception('Erreur lors de la récupération des pièces : ' . $e->getMessage());
        }
    }
}
