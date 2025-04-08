<?php

namespace App\Controller;

use App\Entity\Part;
use App\Service\Ovoko\CheckCarService;
use App\Service\Ovoko\ImportPartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OvokoController extends AbstractController
{
    private CheckCarService $checkCarService;
    private ImportPartService $importPartService;
    private EntityManagerInterface $entityManager;  
    private ParameterBagInterface $params;

    public function __construct(
        CheckCarService $checkCarService,
        ImportPartService $importPartService,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    ) {
        $this->checkCarService = $checkCarService;
        $this->importPartService = $importPartService;
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    /**
     * @Route("/api/ovoko/import-parts", name="import_parts", methods={"POST"})
     */
    public function importParts(): JsonResponse
    {
        try {
            ini_set('memory_limit', '-1');

            $parts = $this->entityManager->getRepository(Part::class)->findAll();

            if (empty($parts)) {
                return new JsonResponse(['error' => 'Aucune pièce à importer.'], 404);
            }

            $results = [];
            $batchSize = 50; // Taille du lot
            $totalParts = count($parts);

            for ($i = 0; $i < $totalParts; $i += $batchSize) {
                $batchParts = array_slice($parts, $i, $batchSize);

                foreach ($batchParts as $part) {
                    try {
                        if (!$part->getExternalId() || !$part->getCategoryName()) {
                            $results[] = [
                                'external_id' => $part->getExternalId() ?? 'unknown',
                                'error' => 'Données de la pièce manquantes ou invalides.',
                                'status' => 'error',
                            ];
                            continue;
                        }

                        if (is_null($part->getModelName()) || trim($part->getModelName()) === '') {
                            $results[] = [
                                'external_id' => $part->getExternalId(),
                                'error' => 'Modèle null ou vide, pièce ignorée.',
                                'status' => 'skipped',
                            ];
                            continue;
                        }

                        $carData = $this->checkCarService->checkPart($part);
                        if (!$carData || !isset($carData['car_id'])) {
                            $results[] = [
                                'external_id' => $part->getExternalId(),
                                'error' => 'Modèle non trouvé ou null, passage à la pièce suivante.',
                                'status' => 'skipped',
                            ];
                            continue;
                        }

                        $carId = (int) $carData['car_id'];

                        $partId = $this->importPartService->importPart(
                            $carId,
                            $part->getExternalId(),
                            $part->getVignette(),
                            $part->getPhotos(),
                            $part->getCategoryName()
                        );

                        $results[] = [
                            'external_id' => $part->getExternalId(),
                            'part_id' => $partId,
                            'status' => 'success',
                        ];
                    } catch (\Exception $e) {
                        $results[] = [
                            'external_id' => $part->getExternalId(),
                            'error' => $e->getMessage(),
                            'status' => 'error',
                        ];
                    }
                }

                $this->entityManager->clear();
            }

            $successCount = count(array_filter($results, fn($result) => $result['status'] === 'success'));
            $errorCount = count($results) - $successCount;

            return new JsonResponse([
                'summary' => [
                    'total' => count($results),
                    'success' => $successCount,
                    'errors' => $errorCount,
                ],
                'results' => $results,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}