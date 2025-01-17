<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\OAuth2\AccessToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class OAuth2TokenService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function generateAccessTokenForUser(User $user): AccessToken
    {
        $accessTokenId = Uuid::v4()->toRfc4122();

        $expiry = (new \DateTime())->modify('+14 days');

        $accessToken = new AccessToken();
        $accessToken->setIdentifier($accessTokenId)
            ->setExpiry($expiry)
            ->setClientIdentifier($user->getClientId())
            ->setUserIdentifier($user->getId())
            ->setScopes(['read', 'write']);

        $this->em->persist($accessToken);

        $user->setApiToken($accessTokenId);
        $user->setTokenExpiresAt($expiry); 

        $this->em->persist($user);
        $this->em->flush();

        return $accessToken;
    }
}
