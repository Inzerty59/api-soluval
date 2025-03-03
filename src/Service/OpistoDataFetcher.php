<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class OpistoDataFetcher
{
    private HttpClientInterface $client;
    private string $apiUrl;
    private AuthenticationService $authService;
    private PartPersistenceService $persistenceService;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        AuthenticationService $authService,
        PartPersistenceService $persistenceService,
        LoggerInterface $logger,
        string $apiUrl
    ) {
        $this->client = $client;
        $this->authService = $authService;
        $this->persistenceService = $persistenceService;
        $this->logger = $logger;
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
        // Augmenter la limite de mémoire et le temps d'exécution
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 0);

        $token = $this->authService->getValidToken();

        $queryParams = array_merge([
            'onlyParts' => "true",
        ], $filters);

        $allParts = [];
        $page = 0;
        $requestCount = 0;

        try {
            do {
                $queryParams['page'] = $page;
                $queryParams['itemsPerPage'] = 100; // Assurez-vous de définir la limite par page

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

                if (isset($data['Parts']) && is_array($data['Parts'])) {
                    $this->saveParts($data['Parts']); 
                    $allParts = array_merge($allParts, $data['Parts']);
                    $this->logger->info('Page ' . $page . ' : ' . count($data['Parts']) . ' pièces récupérées.');
                } else {
                    $this->logger->warning('Page ' . $page . ' : Aucune pièce récupérée.');
                }

                $page++;
                $requestCount++;

                if ($requestCount >= 500) {
                    sleep(60);
                }
            } while (!empty($data['Parts']));

            $this->logger->info('Total pièces récupérées : ' . count($allParts));
            return $allParts;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la récupération des pièces : ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les pièces dans la base de données ou un fichier temporaire.
     *
     * @param array $parts Les pièces à sauvegarder.
     */
    private function saveParts(array $parts): void
    {
        foreach ($parts as $part) {
            $this->persistenceService->persistPart($part);
        }
    }
}
