<?php

namespace App\Entity\OAuth2;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth2_auth_codes")
 */
class AuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100)
     */
    private string $identifier;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expiry;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private string $clientIdentifier;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $userIdentifier = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $scopes = [];

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getExpiry(): \DateTime
    {
        return $this->expiry;
    }

    public function setExpiry(\DateTime $expiry): self
    {
        $this->expiry = $expiry;
        return $this;
    }

    public function getClientIdentifier(): string
    {
        return $this->clientIdentifier;
    }

    public function setClientIdentifier(string $clientIdentifier): self
    {
        $this->clientIdentifier = $clientIdentifier;
        return $this;
    }

    public function getUserIdentifier(): ?int
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(?int $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;
        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }
}
