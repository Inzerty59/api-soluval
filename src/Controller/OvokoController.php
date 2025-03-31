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
            $parts = $this->entityManager->getRepository(Part::class)->findAll();

            if (empty($parts)) {
                return new JsonResponse(['error' => 'Aucune pièce à importer.'], 404);
            }

            $username = $this->params->get('OVOKO_API_USERNAME');
            $password = $this->params->get('OVOKO_API_PASSWORD');
            $userToken = $this->params->get('OVOKO_API_USER_TOKEN');

            $results = [];

            foreach ($parts as $part) {
                try {
                    if (!$part->getExternalId() || !$part->getCategoryName()) {
                        $results[] = [
                            'external_id' => $part->getExternalId() ?? 'unknown',
                            'error' => 'Données de la pièce manquantes ou invalides.',
                            'status' => 'error',
                        ];
                        continue;
                    }

                    $carData = $this->checkCarService->checkPart($part);
                    if (!$carData || !isset($carData['car_id'])) {
                        throw new \Exception("Impossible de trouver ou de créer le véhicule pour la pièce avec l'ID externe : {$part->getExternalId()}");
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