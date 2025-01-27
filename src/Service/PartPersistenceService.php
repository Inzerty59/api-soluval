<?php
namespace App\Service;

use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;

class PartPersistenceService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Persiste une pièce dans la base de données.
     *
     * @param array $partData Les données de la pièce.
     * @return void
     */
    public function persistPart(array $partData): void
    {
        $part = new Part();

        // Mapper les données de l'API à l'entité
        $part->setExternalId($partData['Id'] ?? null)
    ->setManufacturerReference($partData['ManufacturerReference'] ?? null)
    ->setAdaptableReference($partData['AdaptableReference'] ?? null)
    ->setCategoryName($partData['Category']['Name'] ?? null)
    ->setDescription($partData['Description'] ?? null)
    ->setPartCondition($partData['Condition'] ?? null)
    ->setWarranty($partData['Warranty'] ?? null)
    ->setBrandName($partData['Vehicle']['Identification']['Brand']['Name'] ?? null) 
    ->setRangeName($partData['Vehicle']['Identification']['Range']['Name'] ?? null) 
    ->setModelName($partData['Vehicle']['Identification']['Model']['Name'] ?? null)
    ->setFinishName($partData['Vehicle']['Identification']['Finish'] ?? null)
    ->setCommercialDesignation($partData['Vehicle']['Identification']['CommercialDesignation'] ?? null)
    ->setVehicleYear($partData['Vehicle']['Year'] ?? null)
    ->setMileage($partData['Vehicle']['Mileage'] ?? null)
    ->setColorName($partData['Vehicle']['Color'] ?? null)
    ->setDisplacement($partData['Vehicle']['Identification']['Displacement'] ?? null)
    ->setPower($partData['Vehicle']['Identification']['Power'] ?? null)
    ->setEnergyName($partData['Vehicle']['Identification']['Energy']['Name'] ?? null)
    ->setGearboxType($partData['Vehicle']['Identification']['GearboxType'] ?? null)
    ->setEngineCode($partData['Vehicle']['Identification']['EngineCode'] ?? null)
    ->setGearboxCode($partData['Vehicle']['Identification']['GearboxCode'] ?? null)
    ->setDoorNumber($partData['Vehicle']['Identification']['DoorNumber'] ?? null)
    ->setVignette($partData['Vignette'] ?? null)
    ->setPhotos($partData['Photos'] ?? null)
    ->setPrice($partData['Price'] ?? null)
    ->setCasseId($partData['Casse']['Id'] ?? null)
    ->setShippingId($partData['Shipping']['ShippingId'] ?? null);




        // Vérifier l'existence pour éviter les doublons
        $existingPart = $this->entityManager->getRepository(Part::class)
            ->findOneBy(['external_id' => $part->getExternalId()]);

        if (!$existingPart) {
            $this->entityManager->persist($part);
            $this->entityManager->flush(); // Persist et sauvegarde immédiatement
        }
    }
}
