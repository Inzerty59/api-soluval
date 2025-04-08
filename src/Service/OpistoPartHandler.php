<?php

namespace App\Service;

use App\Repository\PartRepository;
use Psr\Cache\CacheItemPoolInterface;

class OpistoPartHandler
{
    private OpistoDataFetcher $dataFetcher;
    private PartPersistenceService $persistenceService;
    private PartRepository $partRepository;
    private CacheItemPoolInterface $cache;

    public function __construct(
        OpistoDataFetcher $dataFetcher,
        PartPersistenceService $persistenceService,
        PartRepository $partRepository,
        CacheItemPoolInterface $cache
    ) {
        $this->dataFetcher = $dataFetcher;
        $this->persistenceService = $persistenceService;
        $this->partRepository = $partRepository;
        $this->cache = $cache;
    }

    /**
     * Récupère et met en cache les données depuis l'API toutes les X minutes.
     *
     * @param array $filters
     * @param int $cacheTtl Durée du cache en secondes (exemple : 3600 pour 1 heure)
     * @return array
     */
    public function fetchAndSavePartsWithCache(array $filters = [], int $cacheTtl = 60): array
    {
        // Utilise une clé unique pour le cache basée sur les filtres
        $cacheKey = 'opisto_parts_' . md5(json_encode($filters));

        // Vérifie si les données sont déjà en cache
        $cachedItem = $this->cache->getItem($cacheKey);
        if (!$cachedItem->isHit()) {
            // Les données ne sont pas en cache, récupère-les depuis l'API
            $apiData = $this->dataFetcher->fetchParts($filters);

            if (isset($apiData['Parts']) && is_array($apiData['Parts'])) {
                foreach ($apiData['Parts'] as $partData) {
                    $this->persistenceService->persistPart($partData);
                }
            }

            // Stocke les données en cache
            $cachedItem->set(true); // Tu peux aussi stocker les données si nécessaire
            $cachedItem->expiresAfter($cacheTtl);
            $this->cache->save($cachedItem);
        }

        // Retourne les données sauvegardées dans la base
        return $this->partRepository->findAll();
    }
}

