<?php

namespace App\Service\Intermobilitas;

use App\Entity\Part;
use App\Repository\PartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class IntermobilitasSyncService
{
    private PartRepository $partRepository;
    private IntermobilitasApiService $apiService;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        PartRepository $partRepository,
        IntermobilitasApiService $apiService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->partRepository = $partRepository;
        $this->apiService = $apiService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function syncAllParts(): array
    {
        $parts = $this->partRepository->findBy(['available' => true]);
        
        $stats = [
            'total' => count($parts),
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];

        $this->logger->info('Starting TotalParts synchronization', ['total' => $stats['total']]);

        foreach ($parts as $part) {
            try {
                if (!$this->isPartValid($part)) {
                    $stats['skipped']++;
                    $this->logger->warning('Part skipped: missing required data', [
                        'external_id' => $part->getExternalId(),
                    ]);
                    continue;
                }

                $itemData = $this->mapPartToTotalParts($part);
                $response = $this->apiService->insertOrUpdateItem($itemData);

                if (isset($response['result'])) {
                    $stats['success']++;
                } else {
                    $stats['errors']++;
                    $this->logger->error('Unexpected API response', [
                        'external_id' => $part->getExternalId(),
                        'response' => $response,
                    ]);
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->logger->error('Part synchronization failed', [
                    'external_id' => $part->getExternalId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('TotalParts synchronization completed', $stats);

        return $stats;
    }

    public function syncPart(Part $part): array
    {
        if (!$this->isPartValid($part)) {
            throw new \InvalidArgumentException('Part has missing required data');
        }

        $itemData = $this->mapPartToTotalParts($part);
        return $this->apiService->insertOrUpdateItem($itemData);
    }

    public function deletePart(int $externalId): array
    {
        return $this->apiService->deleteItem($externalId);
    }

    public function deleteUnavailableParts(): array
    {
        $parts = $this->partRepository->findBy(['available' => false]);
        
        $stats = [
            'total' => count($parts),
            'success' => 0,
            'errors' => 0,
        ];

        $this->logger->info('Starting unavailable parts deletion', ['total' => $stats['total']]);

        foreach ($parts as $part) {
            try {
                $this->deletePart($part->getExternalId());
                $stats['success']++;
            } catch (\Exception $e) {
                $stats['errors']++;
                $this->logger->error('Part deletion failed', [
                    'external_id' => $part->getExternalId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->logger->info('Unavailable parts deletion completed', $stats);

        return $stats;
    }

    private function mapPartToTotalParts(Part $part): array
    {
        $priceData = $part->getPrice();
        $originPrice = $priceData['OriginPrice'] ?? 0;
        $vatRate = $priceData['VatRate'] ?? 1;

        $data = [
            'id' => $part->getExternalId(),
            'partId' => $part->getCategoryId(),
            'description' => $part->getCategoryName(),
            'price' => (float) $originPrice,
            'vat' => (int) $vatRate,
            'warranty' => $part->getWarranty() ?? 6,
        ];

        $vehicleData = [];

        if ($part->getVin()) {
            $vehicleData['vin'] = $part->getVin();
        }

        if ($part->getVehicleYear()) {
            $vehicleData['year'] = $part->getVehicleYear();
        }

        if ($part->getBrandName()) {
            $vehicleData['make'] = $part->getBrandName();
        }

        if ($part->getModelName()) {
            $vehicleData['model'] = $part->getModelName();
        }

        if ($part->getFinishName()) {
            $vehicleData['vehicleDescription'] = $part->getFinishName();
        }

        if ($part->getDoorNumber()) {
            $vehicleData['doorNr'] = $part->getDoorNumber();
        }

        if ($part->getDisplacement()) {
            $vehicleData['engineSize'] = $part->getDisplacement();
        }

        if ($part->getPower() && is_numeric($part->getPower())) {
            $vehicleData['enginePower'] = (int) $part->getPower();
        }

        if ($part->getEngineCode()) {
            $vehicleData['engineCode'] = $part->getEngineCode();
        }

        if ($part->getGearboxCode()) {
            $vehicleData['gearboxCode'] = $part->getGearboxCode();
        }

        if ($part->getEnergyName()) {
            $vehicleData['fuelType'] = $part->getEnergyName();
        }

        if (!empty($vehicleData)) {
            $data['vehicle'] = $vehicleData;
        }

        return $data;
    }

    private function isPartValid(Part $part): bool
    {
        return $part->getExternalId() !== null
            && $part->getCategoryId() !== null
            && $part->getCategoryName() !== null;
    }
}
