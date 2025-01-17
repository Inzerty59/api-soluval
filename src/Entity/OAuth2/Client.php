<?php

namespace App\Entity\OAuth2;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth2_clients")
 */

class Client
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=80)
     */
    private string $identifier;
    /**
     * @ORM\Column(type="json")
     */
    private array $redirectUris = [];
    /**
     * @ORM\Column(type="json")
     */
    private array $grants = [];
    /**
     * @ORM\Column(type="json")
     */
    private array $scopes = [];
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $secret;
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }
    public function setRedirectUris(array $redirectUris): self
    {
        $this->redirectUris = $redirectUris;
        return $this;
    }
    public function getGrants(): array
    {
        return $this->grants;
    }
    public function setGrants(array $grants): self
    {
        $this->grants = $grants;
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
    public function getSecret(): ?string
    {
        return $this->secret;
    }
    public function setSecret(?string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }
}