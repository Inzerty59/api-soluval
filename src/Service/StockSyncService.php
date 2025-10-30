<?php

namespace App\Service;

use App\Service\OpistoApiService;
use App\Service\OvokoApiService;
use App\Service\Intermobilitas\IntermobilitasSyncService;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class StockSyncService
{
    private OpistoApiService $opistoApi;
    private OvokoApiService $ovokoApi;
    private IntermobilitasSyncService $intermobilitasSyncService;
    private LoggerInterface $logger;
    private PartRepository $partRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        OpistoApiService $opistoApi,
        OvokoApiService $ovokoApi,
        IntermobilitasSyncService $intermobilitasSyncService,
        LoggerInterface $logger,
        PartRepository $partRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->opistoApi = $opistoApi;
        $this->ovokoApi = $ovokoApi;
        $this->intermobilitasSyncService = $intermobilitasSyncService;
        $this->logger = $logger;
        $this->partRepository = $partRepository;
        $this->entityManager = $entityManager;
    }

    public function sync(\DateTimeInterface $start, \DateTimeInterface $end): void
    {
        $parts = $this->opistoApi->getPartsDeletedBetween($start, $end);

        $this->logger->info('Nombre de pièces supprimées trouvées : ' . count($parts));

        foreach ($parts as $part) {
            $externalId = $part['Id'] ?? 'N/A';
            
            $this->logger->info("Pièce supprimée $externalId, synchronisation Ovoko et Intermobilitas...");
            
            $this->ovokoApi->markPartAsSold($externalId);
            
            try {
                $response = $this->intermobilitasSyncService->deletePart($externalId);
                
                if (isset($response['result'])) {
                    if ($response['result'] === true) {
                        $this->logger->info("Pièce $externalId supprimée avec succès chez Intermobilitas.");
                    } else {
                        $this->logger->warning("Pièce $externalId introuvable chez Intermobilitas.", [
                            'errors' => $response['errors'] ?? [],
                        ]);
                    }
                } else {
                    $this->logger->error("Réponse inattendue lors de la suppression de la pièce $externalId chez Intermobilitas.", [
                        'response' => $response,
                    ]);
                }
            } catch (\Exception $e) {
                $this->logger->warning("Erreur lors de la suppression de la pièce $externalId chez Intermobilitas : " . $e->getMessage());
            }

            $entity = $this->partRepository->findOneBy(['external_id' => $externalId]);
            if ($entity) {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
                $this->logger->info("Pièce $externalId supprimée de la base locale.");
            } else {
                $this->logger->info("Pièce $externalId non trouvée dans la base locale.");
            }
        }
    }
}