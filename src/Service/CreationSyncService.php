<?php

namespace App\Service;

use App\Service\OpistoApiService;
use App\Service\PartPersistenceService;
use App\Repository\PartRepository;
use App\Service\OpistoStockChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CreationSyncService
{
    private OpistoApiService $opistoApi;
    private PartPersistenceService $persistenceService;
    private LoggerInterface $logger;
    private PartRepository $partRepository;
    private EntityManagerInterface $entityManager;
    private OpistoStockChecker $stockChecker;

    public function __construct(
        OpistoApiService $opistoApi,
        PartPersistenceService $persistenceService,
        LoggerInterface $logger,
        PartRepository $partRepository,
        EntityManagerInterface $entityManager,
        OpistoStockChecker $stockChecker
    ) {
        $this->opistoApi = $opistoApi;
        $this->persistenceService = $persistenceService;
        $this->logger = $logger;
        $this->partRepository = $partRepository;
        $this->entityManager = $entityManager;
        $this->stockChecker = $stockChecker;
    }

    public function sync(\DateTimeInterface $start, \DateTimeInterface $end): void
    {
        $parts = $this->opistoApi->getPartsCreatedBetween($start, $end);

        $this->logger->info('Nombre de nouvelles pièces trouvées : ' . count($parts));

        $batchSize = 20;
        $totalParts = count($parts);
        $processed = 0;
        $ignored = 0;
        $added = 0;
        $updated = 0;

        for ($i = 0; $i < $totalParts; $i += $batchSize) {
            $batch = array_slice($parts, $i, $batchSize);
            $batchNumber = intval($i / $batchSize) + 1;
            $totalBatches = ceil($totalParts / $batchSize);
            
            $this->logger->info("Traitement du batch $batchNumber/$totalBatches (" . count($batch) . " pièces)");

            foreach ($batch as $part) {
                $externalId = $part['Id'] ?? 'N/A';
                
                $isStillAvailable = $this->stockChecker->checkStock($externalId);
                
                if (!$isStillAvailable) {
                    $this->logger->warning("Pièce $externalId créée mais déjà supprimée, ignorée.");
                    $ignored++;
                    continue;
                }
                
                $existingPart = $this->partRepository->findOneBy(['external_id' => $externalId]);
                
                if ($existingPart) {
                    $this->logger->info("Pièce $externalId existe déjà, mise à jour...");
                    $this->persistenceService->persistPart($part);
                    $updated++;
                } else {
                    $this->logger->info("Nouvelle pièce $externalId, ajout en base...");
                    $this->persistenceService->persistPart($part);
                    $added++;
                }
                
                $processed++;
            }

            if ($i + $batchSize < $totalParts) {
                $this->logger->info("Pause de 2 secondes avant le batch suivant...");
                sleep(2);
            }
        }
        
        $this->logger->info("Synchronisation terminée. Traité: $processed, Ajouté: $added, Mis à jour: $updated, Ignoré: $ignored");
    }
}
