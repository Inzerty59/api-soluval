<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?bool $ToSend = null;

    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?bool $IsFreeShipping = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private ?BillingAdress $billingAdress = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private ?DeliveryAdress $deliveryAdress = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private ?MangoPay $mangoPay = null;

    /**
     * @var Collection<int, Part>
     */
    #[ORM\OneToMany(targetEntity: Part::class, mappedBy: 'order')]
    #[ORM\ManyToOne(targetEntity: Part::class, cascade: ['persist'])]
    #[Groups(['order:read', 'order:write'])]
    #[MaxDepth(1)]
    private Collection $parts;

    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?\DateTimeImmutable $UpdatedAt = null;

    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(['order:read', 'order:write'])]
    private array $status = [];

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $orderNumber = null;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isToSend(): ?bool
    {
        return $this->ToSend;
    }

    public function setToSend(bool $ToSend): static
    {
        $this->ToSend = $ToSend;

        return $this;
    }

    public function isFreeShipping(): ?bool
    {
        return $this->IsFreeShipping;
    }

    public function setFreeShipping(bool $IsFreeShipping): static
    {
        $this->IsFreeShipping = $IsFreeShipping;

        return $this;
    }

    /**
     * @return Collection<int, Part>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Part $part): static
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->setOrder($this);
        }

        return $this;
    }

    public function removePart(Part $part): static
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getOrder() === $this) {
                $part->setOrder(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBillingAdress(): ?BillingAdress
    {
        return $this->billingAdress;
    }

    public function setBillingAdress(?BillingAdress $billingAdress): static
    {
        $this->billingAdress = $billingAdress;

        return $this;
    }

    public function getDeliveryAdress(): ?DeliveryAdress
    {
        return $this->deliveryAdress;
    }

    public function setDeliveryAdress(?DeliveryAdress $deliveryAdress): static
    {
        $this->deliveryAdress = $deliveryAdress;

        return $this;
    }

    public function getMangoPay(): ?MangoPay
    {
        return $this->mangoPay;
    }

    public function setMangoPay(?MangoPay $mangoPay): static
    {
        $this->mangoPay = $mangoPay;

        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): static
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->CreatedAt = new \DateTimeImmutable();
        $this->UpdatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->UpdatedAt = new \DateTimeImmutable();
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getTotalShippingCostsTTC(): float
    {
        if ($this->IsFreeShipping) {
            return 0.0;
        }

        $shipping = $this->getDeliveryAdress()?->getShipping();
        if ($shipping) {
            $shippingCosts = $shipping->getShippingCosts();
            $totalShippingCost = 0.0;

            foreach ($this->parts as $part) {
                $totalShippingCost += $shippingCosts['TTC'];
            }

            return $totalShippingCost;
        }

        return 0.0;
    }

    public function getTotalPartsPrice(): float
    {
        $total = 0.0;
        foreach ($this->parts as $part) {
            $total += $part->getFinalPrice();
        }
        return $total;
    }

    public function getNetToPay(): float
    {
        return $this->getTotalPartsPrice() + $this->getTotalShippingCostsTTC();
    }
}