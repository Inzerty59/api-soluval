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

    #[ORM\Column]
    private ?int $Phone = null;

    #[ORM\Column(length: 255)]
    private ?string $Street = null;

    #[ORM\Column(length: 255)]
    private ?string $StreetAdditionnal = null;

    #[ORM\Column(length: 255)]
    private ?string $PostCode = null;

    #[ORM\Column(length: 255)]
    private ?string $City = null;

    #[ORM\Column]
    private ?int $CountryId = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'billingAdress')]
    private Collection $category;

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

    public function getPhone(): ?int
    {
        return $this->Phone;
    }

    public function setPhone(int $Phone): static
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

    public function setStreetAdditionnal(string $StreetAdditionnal): static
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

    public function getCountryId(): ?int
    {
        return $this->CountryId;
    }

    public function setCountryId(int $CountryId): static
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
}
