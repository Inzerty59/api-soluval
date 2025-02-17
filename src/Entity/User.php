<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $surname = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(type: 'string', length: 14, nullable: true)]
    private ?string $siretNumber = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(type: 'string')]
    private string $accountType;

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    /**
     * @var Collection<int, BillingAdress>
     */
    #[ORM\OneToMany(targetEntity: BillingAdress::class, mappedBy: 'user')]
    private Collection $billingAdresses;

    /**
     * @var Collection<int, DeliveryAdress>
     */
    #[ORM\OneToMany(targetEntity: DeliveryAdress::class, mappedBy: 'user')]
    private Collection $deliveryAdresses;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->billingAdresses = new ArrayCollection();
        $this->deliveryAdresses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function setAccountType(string $accountType): self
    {
        $this->accountType = $accountType;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getSiretNumber(): ?string
    {
        return $this->siretNumber;
    }

    public function setSiretNumber(?string $siretNumber): self
    {
        $this->siretNumber = $siretNumber;
        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BillingAdress>
     */
    public function getBillingAdresses(): Collection
    {
        return $this->billingAdresses;
    }

    public function addBillingAdress(BillingAdress $billingAdress): static
    {
        if (!$this->billingAdresses->contains($billingAdress)) {
            $this->billingAdresses->add($billingAdress);
            $billingAdress->setUser($this);
        }

        return $this;
    }

    public function removeBillingAdress(BillingAdress $billingAdress): static
    {
        if ($this->billingAdresses->removeElement($billingAdress)) {
            // set the owning side to null (unless already changed)
            if ($billingAdress->getUser() === $this) {
                $billingAdress->setUser(null);
            }
        }

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
            $deliveryAdress->setUser($this);
        }

        return $this;
    }

    public function removeDeliveryAdress(DeliveryAdress $deliveryAdress): static
    {
        if ($this->deliveryAdresses->removeElement($deliveryAdress)) {
            // set the owning side to null (unless already changed)
            if ($deliveryAdress->getUser() === $this) {
                $deliveryAdress->setUser(null);
            }
        }

        return $this;
    }
}