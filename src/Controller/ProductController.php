<?php

namespace App\Controller;

use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/produit/{id}', name: 'product_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les informations du produit en fonction de l'ID
        $part = $entityManager->getRepository(Part::class)->find($id);

        if (!$part) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        // Passer les informations récupérées au template Twig
        return $this->render('product/detail.html.twig', [
            'part' => $part,
        ]);
    }

    /**
     * Récupère une pièce par son external_id via API
     */
    #[Route('/api/parts/external/{externalId}', name: 'api_part_by_external_id', methods: ['GET'])]
    public function getPartByExternalId(string $externalId, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $part = $entityManager->getRepository(Part::class)
                ->findOneBy(['external_id' => $externalId]);

            if (!$part) {
                return new JsonResponse([
                    'error' => 'Pièce non trouvée',
                    'external_id' => $externalId
                ], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'external_id' => $part->getExternalId(),
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
                    'Price' => $part->getPrice(),
                    'casse_id' => $part->getCasseId(),
                    'Shipping_id' => $part->getShippingId(),
                    'weight' => $part->getWeight(),
                    'vin' => $part->getVin()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de la pièce',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}