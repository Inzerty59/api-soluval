<?php

namespace App\Entity;

use App\Repository\ShippingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShippingsRepository::class)]
class Shippings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ShippingId = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column]
    private ?int $Coefficient = null;

    #[ORM\Column(length: 255)]
    private ?string $Cost = null;

    #[ORM\Column(length: 255)]
    private ?string $CostExcludingTaxes = null;

    #[ORM\Column]
    private ?bool $IsDeliveryAvailable = null;

    #[ORM\Column(length: 255)]
    private ?string $VATRate = null;

    #[ORM\Column(length: 255)]
    private ?string $DelayMin = null;

    #[ORM\Column(length: 255)]
    private ?string $DelayMax = null;

    #[ORM\Column]
    private ?int $CountryId = null;

    #[ORM\Column(length: 255)]
    private ?string $ISOCode = null;

    #[ORM\Column(length: 255)]
    private ?string $DiscountPart2 = null;

    #[ORM\Column(length: 255)]
    private ?string $DiscountPart3 = null;

    /**
     * @var Collection<int, DeliveryAdress>
     */
    #[ORM\OneToMany(targetEntity: DeliveryAdress::class, mappedBy: 'shipping')]
    private Collection $deliveryAdresses;

    public function __construct()
    {
        $this->deliveryAdresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippingId(): ?int
    {
        return $this->ShippingId;
    }

    public function setShippingId(int $ShippingId): static
    {
        $this->ShippingId = $ShippingId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getCoefficient(): ?int
    {
        return $this->Coefficient;
    }

    public function setCoefficient(int $Coefficient): static
    {
        $this->Coefficient = $Coefficient;

        return $this;
    }

    public function getCost(): ?string
    {
        return $this->Cost;
    }

    public function setCost(string $Cost): static
    {
        $this->Cost = $Cost;

        return $this;
    }

    public function getCostExcludingTaxes(): ?string
    {
        return $this->CostExcludingTaxes;
    }

    public function setCostExcludingTaxes(string $CostExcludingTaxes): static
    {
        $this->CostExcludingTaxes = $CostExcludingTaxes;

        return $this;
    }

    public function isDeliveryAvailable(): ?bool
    {
        return $this->IsDeliveryAvailable;
    }

    public function setDeliveryAvailable(bool $IsDeliveryAvailable): static
    {
        $this->IsDeliveryAvailable = $IsDeliveryAvailable;

        return $this;
    }

    public function getVATRate(): ?string
    {
        return $this->VATRate;
    }

    public function setVATRate(string $VATRate): static
    {
        $this->VATRate = $VATRate;

        return $this;
    }

    public function getDelayMin(): ?string
    {
        return $this->DelayMin;
    }

    public function setDelayMin(string $DelayMin): static
    {
        $this->DelayMin = $DelayMin;

        return $this;
    }

    public function getDelayMax(): ?string
    {
        return $this->DelayMax;
    }

    public function setDelayMax(string $DelayMax): static
    {
        $this->DelayMax = $DelayMax;

        return $this;
    }

    public function getCountryId(): ?int
    {
        return $this->CountryId;
    }

    public function setCountryId(int $CountryId): static
    {
        $this->CountryId = $CountryId;

        return $this;
    }

    public function getISOCode(): ?string
    {
        return $this->ISOCode;
    }

    public function setISOCode(string $ISOCode): static
    {
        $this->ISOCode = $ISOCode;

        return $this;
    }

    public function getDiscountPart2(): ?string
    {
        return $this->DiscountPart2;
    }

    public function setDiscountPart2(string $DiscountPart2): static
    {
        $this->DiscountPart2 = $DiscountPart2;

        return $this;
    }

    public function getDiscountPart3(): ?string
    {
        return $this->DiscountPart3;
    }

    public function setDiscountPart3(string $DiscountPart3): static
    {
        $this->DiscountPart3 = $DiscountPart3;

        return $this;
    }

    /**
     * @return Collection<int, DeliveryAdress>
     */
    public function getDeliveryAdresses(): Collection
    {
        return $this->deliveryAdresses;
    }

    public function addDeliveryAdress(DeliveryAdress $deliveryAdress): static
    {
        if (!$this->deliveryAdresses->contains($deliveryAdress)) {
            $this->deliveryAdresses->add($deliveryAdress);
            $deliveryAdress->setShipping($this);
        }

        return $this;
    }

    public function removeDeliveryAdress(DeliveryAdress $deliveryAdress): static
    {
        if ($this->deliveryAdresses->removeElement($deliveryAdress)) {
            // set the owning side to null (unless already changed)
            if ($deliveryAdress->getShipping() === $this) {
                $deliveryAdress->setShipping(null);
            }
        }

        return $this;
    }
}
