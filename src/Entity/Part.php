<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\PartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ]
)]class Part
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $external_id = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $manufacturer_reference = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $adaptable_reference = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $category_name = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $part_condition = null;

    #[ORM\Column(nullable: true)]
    private ?int $warranty = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $brand_name = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $range_name = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $model_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $finish_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commercial_designation = null;

    #[ORM\Column(nullable: true)]
    private ?int $vehicle_year = null;

    #[ORM\Column(nullable: true)]
    private ?int $mileage = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $color_name = null;

    #[ORM\Column(nullable: true)]
    private ?int $displacement = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $power = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $energy_name = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $gearbox_type = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $engine_code = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $gearbox_code = null;

    #[ORM\Column(nullable: true)]
    private ?int $door_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vignette = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $photos = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $Price = null;

    #[ORM\Column]
    private ?int $casse_id = null;

    #[ORM\Column]
    private ?int $Shipping_id = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\ManyToMany(targetEntity: Order::class, inversedBy: 'parts')]
    private Collection $category;

    #[ORM\Column(length: 255)]
    private ?string $weight = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExternalId(): ?int
    {
        return $this->external_id;
    }

    public function setExternalId(int $external_id): static
    {
        $this->external_id = $external_id;

        return $this;
    }

    public function getManufacturerReference(): ?string
    {
        return $this->manufacturer_reference;
    }

    public function setManufacturerReference(?string $manufacturer_reference): static
    {
        $this->manufacturer_reference = $manufacturer_reference;

        return $this;
    }

    public function getAdaptableReference(): ?string
    {
        return $this->adaptable_reference;
    }

    public function setAdaptableReference(?string $adaptable_reference): static
    {
        $this->adaptable_reference = $adaptable_reference;

        return $this;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function setCategoryName(?string $category_name): static
    {
        $this->category_name = $category_name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPartCondition(): ?int
    {
        return $this->part_condition;
    }

    public function setPartCondition(?int $part_condition): static
    {
        $this->part_condition = $part_condition;

        return $this;
    }

    public function getWarranty(): ?int
    {
        return $this->warranty;
    }

    public function setWarranty(?int $warranty): static
    {
        $this->warranty = $warranty;

        return $this;
    }

    public function getBrandName(): ?string
    {
        return $this->brand_name;
    }

    public function setBrandName(?string $brand_name): static
    {
        $this->brand_name = $brand_name;

        return $this;
    }

    public function getRangeName(): ?string
    {
        return $this->range_name;
    }

    public function setRangeName(?string $range_name): static
    {
        $this->range_name = $range_name;

        return $this;
    }

    public function getModelName(): ?string
    {
        return $this->model_name;
    }

    public function setModelName(?string $model_name): static
    {
        $this->model_name = $model_name;

        return $this;
    }

    public function getFinishName(): ?string
    {
        return $this->finish_name;
    }

    public function setFinishName(?string $finish_name): static
    {
        $this->finish_name = $finish_name;

        return $this;
    }

    public function getCommercialDesignation(): ?string
    {
        return $this->commercial_designation;
    }

    public function setCommercialDesignation(?string $commercial_designation): static
    {
        $this->commercial_designation = $commercial_designation;

        return $this;
    }

    public function getVehicleYear(): ?int
    {
        return $this->vehicle_year;
    }

    public function setVehicleYear(?int $vehicle_year): static
    {
        $this->vehicle_year = $vehicle_year;

        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): static
    {
        $this->mileage = $mileage;

        return $this;
    }

    public function getColorName(): ?array
    {
        return $this->color_name;
    }

    public function setColorName(?array $color_name): static
    {
        $this->color_name = $color_name;

        return $this;
    }
    

    public function getDisplacement(): ?int
    {
        return $this->displacement;
    }

    public function setDisplacement(?int $displacement): static
    {
        $this->displacement = $displacement;

        return $this;
    }

    public function getPower(): ?string
    {
        return $this->power;
    }

    public function setPower(?string $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getEnergyName(): ?string
    {
        return $this->energy_name;
    }

    public function setEnergyName(?string $energy_name): static
    {
        $this->energy_name = $energy_name;

        return $this;
    }

    public function getGearboxType(): ?array
    {
        return $this->gearbox_type;
    }

    public function setGearboxType(?array $gearbox_type): static
    {
        $this->gearbox_type = $gearbox_type;

        return $this;
    }

    public function getEngineCode(): ?string
    {
        return $this->engine_code;
    }

    public function setEngineCode(?string $engine_code): static
    {
        $this->engine_code = $engine_code;

        return $this;
    }

    public function getGearboxCode(): ?string
    {
        return $this->gearbox_code;
    }

    public function setGearboxCode(?string $gearbox_code): static
    {
        $this->gearbox_code = $gearbox_code;

        return $this;
    }

    public function getDoorNumber(): ?int
    {
        return $this->door_number;
    }

    public function setDoorNumber(?int $door_number): static
    {
        $this->door_number = $door_number;

        return $this;
    }

    public function getVignette(): ?string
    {
        return $this->vignette;
    }

    public function setVignette(?string $vignette): static
    {
        $this->vignette = $vignette;

        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): static
    {
        $this->photos = $photos;

        return $this;
    }

    public function getPrice(): ?array
    {
        return $this->Price;
    }

    public function setPrice(?array $Price): static
    {
        $this->Price = $Price;

        return $this;
    }

    
    public function getName(): string
    {   
    $cleanCategoryName = preg_replace('/\s*\(.*?\)\s*/', '', $this->category_name);
    return trim(sprintf(
        '%s %s %s %s',
        $cleanCategoryName,
        $this->brand_name,
        $this->model_name,
        $this->energy_name
    ));
    }

    public function getFinalPrice(): ?string
    {
    if (is_array($this->Price) && isset($this->Price['OriginPrice'], $this->Price['VATRate'])) {
        $originPrice = $this->Price['OriginPrice'];
        $vatRate = $this->Price['VATRate'];
        $finalPrice = $originPrice + ($originPrice * $vatRate / 100);
        $finalPrice = floor($finalPrice * 100) / 100;
        return number_format($finalPrice, 2, '.', '');
    }
    return null;
    }

    public function getPriceHT(): ?string
    {
    if (is_array($this->Price) && isset($this->Price['OriginPrice'])) {
        $originPrice = $this->Price['OriginPrice'];
        return number_format($originPrice, 2, '.', '');
    }
    return null;
    }

    public function getEstimatedVAT(): ?string
    {
    $finalPrice = $this->getFinalPrice();
    $priceHT = $this->getPriceHT();

    if ($finalPrice && $priceHT) {
        $vatAmount = $finalPrice - $priceHT;

        return number_format($vatAmount, 2, '.', '');
    }
    return null;
    }


    public function getCasseId(): ?int
    {
        return $this->casse_id;
    }

    public function setCasseId(int $casse_id): static
    {
        $this->casse_id = $casse_id;

        return $this;
    }

    public function getShippingId(): ?int
    {
        return $this->Shipping_id;
    }

    public function setShippingId(int $Shipping_id): static
    {
        $this->Shipping_id = $Shipping_id;

        return $this;
    }

    public function getCategory(): ?Order
    {
        return $this->category;
    }

    public function setCategory(?Order $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function addCategory(Order $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Order $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(string $weight): static
    {
        $this->weight = $weight;

        return $this;
    }
}
