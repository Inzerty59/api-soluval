<?php

namespace App\Service;

use App\Service\OpistoApiService;
use App\Service\OvokoApiService;
use Psr\Log\LoggerInterface;

class StockSyncService
{
    private OpistoApiService $opistoApi;
    private OvokoApiService $ovokoApi;
    private LoggerInterface $logger;

    public function __construct(
        OpistoApiService $opistoApi,
        OvokoApiService $ovokoApi,
        LoggerInterface $logger
    ) {
        $this->opistoApi = $opistoApi;
        $this->ovokoApi = $ovokoApi;
        $this->logger = $logger;
    }

    public function sync(\DateTimeInterface $start, \DateTimeInterface $end): void
    {
        $parts = $this->opistoApi->getPartsDeletedBetween($start, $end);

        $this->logger->info('Nombre de pièces supprimées trouvées : ' . count($parts));

        foreach ($parts as $part) {
            $externalId = $part['Id'] ?? 'N/A';
            $this->logger->info("Pièce supprimée $externalId, synchronisation Ovoko...");
            $this->ovokoApi->markPartAsSold($externalId);
        }
    }
}