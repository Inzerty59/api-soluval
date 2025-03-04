<?php

namespace App\Entity;

use App\Repository\BillingAdressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingAdressRepository::class)]
class BillingAdress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $Lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $Phone = null;

    #[ORM\Column(length: 255)]
    private ?string $Street = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $StreetAdditionnal = null;

    #[ORM\Column(length: 255)]
    private ?string $PostCode = null;

    #[ORM\Column(length: 255)]
    private ?string $City = null;

    #[ORM\Column(length: 255)]
    private ?string $CountryId = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'billingAdress')]
    private Collection $category;

    #[ORM\ManyToOne(inversedBy: 'billingAdresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Shippings::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shippings $shipping = null;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(string $Firstname): static
    {
        $this->Firstname = $Firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->Lastname;
    }

    public function setLastname(string $Lastname): static
    {
        $this->Lastname = $Lastname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->Phone;
    }

    public function setPhone(string $Phone): static
    {
        $this->Phone = $Phone;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->Street;
    }

    public function setStreet(string $Street): static
    {
        $this->Street = $Street;

        return $this;
    }

    public function getStreetAdditionnal(): ?string
    {
        return $this->StreetAdditionnal;
    }

    public function setStreetAdditionnal(?string $StreetAdditionnal): static
    {
        $this->StreetAdditionnal = $StreetAdditionnal;

        return $this;
    }

    public function getPostCode(): ?string
    {
        return $this->PostCode;
    }

    public function setPostCode(string $PostCode): static
    {
        $this->PostCode = $PostCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(string $City): static
    {
        $this->City = $City;

        return $this;
    }

    public function getCountryId(): ?string
    {
        return $this->CountryId;
    }

    public function setCountryId(string $CountryId): static
    {
        $this->CountryId = $CountryId;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Order $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
            $category->setBillingAdress($this);
        }

        return $this;
    }

    public function removeCategory(Order $category): static
    {
        if ($this->category->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getBillingAdress() === $this) {
                $category->setBillingAdress(null);
            }
        }

        return $this;
    }

    public function getCountryName(): string
    {
        if ($this->shipping) {
            return $this->shipping->getTitle();
        }

        return $countries[$this->CountryId] ?? 'Inconnu';
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

    public function getShipping(): ?Shippings
    {
        return $this->shipping;
    }

    public function setShipping(?Shippings $shipping): static
    {
        $this->shipping = $shipping;

        if ($shipping) {
            $this->CountryId = $shipping->getCountryId();
        }

        return $this;
    }

    public function getFullAddress(): string
    {
        $address = $this->getFirstname() . ' ' . $this->getLastname() . "\n";
        $address .= $this->getStreet() . "\n";
        if ($this->getStreetAdditionnal()) {
            $address .= $this->getStreetAdditionnal() . "\n";
        }
        $address .= $this->getPostCode() . ' ' . $this->getCity() . "\n";
        $address .= $this->getCountryName();

        return $address;
    }
    
    public function __toString(): string
    {
        return $this->getFullAddress();
    }
}
