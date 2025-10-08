<?php
namespace App\Service;

use App\Entity\Part;
use App\Entity\Shippings;
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
        // Ne persiste que si la pièce est disponible
        if (empty($partData['Available']) || $partData['Available'] !== true) {
            // Optionnel : supprimer la pièce si elle existe déjà dans la base
            $existingPart = $this->entityManager->getRepository(Part::class)
                ->findOneBy(['external_id' => $partData['Id'] ?? null]);
            if ($existingPart) {
                $this->entityManager->remove($existingPart);
                $this->entityManager->flush();
            }
            return;
        }

        $part = new Part();

        // Mapper les données de l'API à l'entité
        $part->setExternalId($partData['Id'] ?? null)
            ->setManufacturerReference($partData['ManufacturerReference'] ?? null)
            ->setAdaptableReference($partData['AdaptableReference'] ?? null)
            ->setCategoryId($partData['Category']['Id'] ?? null)
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
            ->setShippingId($partData['Shipping']['ShippingId'] ?? null)
            ->SetWeight($partData['Weight'] ?? null)
            ->setOrigin($partData['Category']['DataOrigin'] ?? null)
            ->setAvailable($partData['Available'] ?? null)
            ->setVin($partData['Vehicle']['VIN'] ?? null);

        // Vérifier l'existence pour éviter les doublons
        $existingPart = $this->entityManager->getRepository(Part::class)
            ->findOneBy(['external_id' => $part->getExternalId()]);

        if ($existingPart) {
            // Mettre à jour les propriétés de la pièce existante
            $existingPart->setManufacturerReference($partData['ManufacturerReference'] ?? null)
                ->setAdaptableReference($partData['AdaptableReference'] ?? null)
                ->setCategoryId($partData['Category']['Id'] ?? null)
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
                ->setShippingId($partData['Shipping']['ShippingId'] ?? null)
                ->SetWeight($partData['Weight'] ?? null)
                ->setOrigin($partData['Category']['DataOrigin'] ?? null)
                ->setAvailable($partData['Available'] ?? null)
                ->setVin($partData['Vehicle']['VIN'] ?? null);

        } else {
            $this->entityManager->persist($part);
        }

        $this->entityManager->flush(); // Persist et sauvegarde immédiatement

        /*
        if (isset($partData['Shippings']) && is_array($partData['Shippings'])) {
            foreach ($partData['Shippings'] as $shippingData) {
                $this->persistShipping($shippingData);
            }
        }
        */
    }

    /**
     * Persiste un shipping dans la base de données.
     *
     * @param array $shippingData Les données du shipping.
     * @return void
     */
    public function persistShipping(array $shippingData): void
    {
        $shipping = new Shippings();

        $shipping->setShippingId($shippingData['ShippingId'] ?? null)
            ->setTitle($shippingData['Title'] ?? null)
            ->setCoefficient($shippingData['Coefficient'] ?? null)
            ->setCost($shippingData['Cost'] ?? null)
            ->setCostExcludingTaxes($shippingData['CostExcludingTaxes'] ?? null)
            ->setDeliveryAvailable($shippingData['IsDeliveryAvailable'] ?? null)
            ->setVATRate($shippingData['VATRate'] ?? null)
            ->setDelayMin($shippingData['DelayMin'] ?? null)
            ->setDelayMax($shippingData['DelayMax'] ?? null)
            ->setCountryId($shippingData['CountryId'] ?? null)
            ->setISOCode($shippingData['ISOCode'] ?? null)
            ->setDiscountPart2($shippingData['DiscountPart2'] ?? null)
            ->setDiscountPart3($shippingData['DiscountPart3'] ?? null);

        // Vérifier l'existence pour éviter les doublons
        $existingShipping = $this->entityManager->getRepository(Shippings::class)
            ->findOneBy(['ShippingId' => $shipping->getShippingId()]);

        if ($existingShipping) {
            // Mettre à jour les propriétés de la livraison existante
            $existingShipping->setTitle($shippingData['Title'] ?? null)
                ->setCoefficient($shippingData['Coefficient'] ?? null)
                ->setCost($shippingData['Cost'] ?? null)
                ->setCostExcludingTaxes($shippingData['CostExcludingTaxes'] ?? null)
                ->setDeliveryAvailable($shippingData['IsDeliveryAvailable'] ?? null)
                ->setVATRate($shippingData['VATRate'] ?? null)
                ->setDelayMin($shippingData['DelayMin'] ?? null)
                ->setDelayMax($shippingData['DelayMax'] ?? null)
                ->setCountryId($shippingData['CountryId'] ?? null)
                ->setISOCode($shippingData['ISOCode'] ?? null)
                ->setDiscountPart2($shippingData['DiscountPart2'] ?? null)
                ->setDiscountPart3($shippingData['DiscountPart3'] ?? null);
        } else {
            $this->entityManager->persist($shipping);
        }

        $this->entityManager->flush(); // Persist et sauvegarde immédiatement
    }
}
