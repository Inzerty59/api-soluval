<?php

namespace App\Controller;

use App\Service\OpistoPartHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OpistoController extends AbstractController
{
    private OpistoPartHandler $opistoPartHandler;

    public function __construct(OpistoPartHandler $opistoPartHandler)
    {
        $this->opistoPartHandler = $opistoPartHandler;
    }

    #[Route('/opisto/parts', name: 'fetch_opisto_parts', methods: ['GET'])]
    public function fetchParts(Request $request): JsonResponse
    {
        $filters = $request->query->all();

        try {
            // Appeler le service avec un TTL de 1 heure (3600 secondes)
            $parts = $this->opistoPartHandler->fetchAndSavePartsWithCache($filters, 3600);

            // Transformer les entités en format JSON
            $formattedParts = array_map(fn($part) => [
                'id' => $part->getId(),
                'manufacturer_reference' => $part->getManufacturerReference(),
                'adaptable_reference' => $part->getAdaptableReference(),
                'category_name' => $part->getCategoryName(),
                'description' => $part->getDescription(),
                'part_condition' => $part->getPartCondition(),
                'warranty' => $part->getWarranty(),
                'brand_name' => $part->getBrandName(),
                'range_name' => $part->getRangeName(),
                'model_name' => $part->getModelName(),
                'finish_name' => $part->getFinishName(),
                'commercial_designation' => $part->getCommercialDesignation(),
                'vehicle_year' => $part->getVehicleYear(),
                'mileage' => $part->getMileage(),
                'color_name' => $part->getColorName(),
                'displacement' => $part->getDisplacement(),
                'power' => $part->getPower(),
                'energy_name' => $part->getEnergyName(),
                'gearbox_type' => $part->getGearboxType(),
                'engine_code' => $part->getEngineCode(),
                'gearbox_code' => $part->getGearboxCode(),
                'door_number' => $part->getDoorNumber(),
                'vignette' => $part->getVignette(),
                'photos' => $part->getPhotos(),
                'price' => $part->getPrice(),
                'casse_id' => $part->getCasseId(),
                'shipping_id' => $part->getShippingId(),
                'weight' => $part->getWeight(),
                'origin' => $part->getOrigin(),
            ], $parts);

            return new JsonResponse($formattedParts);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
