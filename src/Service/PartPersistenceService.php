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
    ->setBrandName($partData['Vehicle']['Identification']['Brand']['Name'] ?? null) // Remplacé par Vehicle
    ->setRangeName($partData['Vehicle']['Identification']['Range']['Name'] ?? null) // Remplacé par Vehicle
    ->setModelName($partData['Vehicle']['Identification']['Model']['Name'] ?? null) // Remplacé par Vehicle
    ->setFinishName($partData['Vehicle']['Identification']['Finish'] ?? null) // Remplacé par Vehicle
    ->setCommercialDesignation($partData['Vehicle']['Identification']['CommercialDesignation'] ?? null) // Remplacé par Vehicle
    ->setVehicleYear($partData['Vehicle']['Year'] ?? null) // Remplacé par Vehicle
    ->setMileage($partData['Vehicle']['Mileage'] ?? null) // Remplacé par Vehicle
    ->setColorName($partData['Vehicle']['Color'] ?? null) // Remplacé par Vehicle
    ->setDisplacement($partData['Vehicle']['Identification']['Displacement'] ?? null) // Remplacé par Vehicle
    ->setPower($partData['Vehicle']['Identification']['Power'] ?? null) // Remplacé par Vehicle
    ->setEnergyName($partData['Vehicle']['Identification']['Energy']['Name'] ?? null) // Remplacé par Vehicle
    ->setGearboxType($partData['Vehicle']['Identification']['GearboxType'] ?? null) // Remplacé par Vehicle
    ->setEngineCode($partData['Vehicle']['Identification']['EngineCode'] ?? null) // Remplacé par Vehicle
    ->setGearboxCode($partData['Vehicle']['Identification']['GearboxCode'] ?? null) // Remplacé par Vehicle
    ->setDoorNumber($partData['Vehicle']['Identification']['DoorNumber'] ?? null) // Remplacé par Vehicle
    ->setVignette($partData['Vignette'] ?? null)
    ->setPhotos($partData['Photos'] ?? null)
    ->setPrice($partData['Price'] ?? null);


        // Vérifier l'existence pour éviter les doublons
        $existingPart = $this->entityManager->getRepository(Part::class)
            ->findOneBy(['external_id' => $part->getExternalId()]);

        if (!$existingPart) {
            $this->entityManager->persist($part);
            $this->entityManager->flush(); // Persist et sauvegarde immédiatement
        }
    }
}
