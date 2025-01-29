<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks] // Ajout pour activer les callbacks
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $ToSend = null;

    #[ORM\Column]
    private ?bool $IsFreeShipping = null;

    #[ORM\ManyToOne(inversedBy: 'category')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'category')]
    private ?BillingAdress $billingAdress = null;

    #[ORM\ManyToOne(inversedBy: 'category')]
    private ?DeliveryAdress $deliveryAdress = null;

    #[ORM\ManyToOne(inversedBy: 'category')]
    private ?MangoPay $mangoPay = null;

    /**
     * @var Collection<int, Part>
     */
    #[ORM\ManyToMany(targetEntity: Part::class, mappedBy: 'category')]
    private Collection $parts;

    #[ORM\Column]
<<<<<<< HEAD
    private ?\DateTimeImmutable $CreatedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $UpdatedAt = null;
=======
    private ?int $order_number = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $status = [];
>>>>>>> 9f03a0d108e8dc4ecd4bed8be766230b4109a7ec

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
            $part->setCategory($this);
        }

        return $this;
    }

    public function removePart(Part $part): static
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getCategory() === $this) {
                $part->setCategory(null);
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

<<<<<<< HEAD
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): static
    {
        $this->CreatedAt = $CreatedAt;
=======
    public function getOrderNumber(): ?int
    {
        return $this->order_number;
    }

    public function setOrderNumber(int $order_number): static
    {
        $this->order_number = $order_number;
>>>>>>> 9f03a0d108e8dc4ecd4bed8be766230b4109a7ec

        return $this;
    }

<<<<<<< HEAD
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
=======
    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): static
    {
        $this->status = $status;

        return $this;
    }
>>>>>>> 9f03a0d108e8dc4ecd4bed8be766230b4109a7ec
}
