<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\OAuth2\Client;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createUserWithClient(User $user): void
    {
        $this->entityManager->persist($user);

        $client = new Client();
        $client->setIdentifier($user->getClientId());
        $client->setName($user->getName() . ' ' . $user->getSurname());
        $client->setSecret($user->getSecretId());
        $client->setGrants(['password']);
        $client->setScopes(['user']);
        $client->setActive(true);

        $this->entityManager->persist($client);

        $this->entityManager->flush();
    }
}
