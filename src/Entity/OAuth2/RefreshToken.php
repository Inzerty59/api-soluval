<?php

namespace App\Entity\OAuth2;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oauth2_refresh_tokens")
 */
class RefreshToken
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
     * @ORM\Column(type="string", length=100)
     */
    private string $accessTokenIdentifier;

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

    public function getAccessTokenIdentifier(): string
    {
        return $this->accessTokenIdentifier;
    }

    public function setAccessTokenIdentifier(string $accessTokenIdentifier): self
    {
        $this->accessTokenIdentifier = $accessTokenIdentifier;
        return $this;
    }
}
