<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class OAuth2LoginService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function login(string $email, string $password): string
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new \Exception('Identifiants incorrects.');
        }

        $now = new \DateTime();
        if ($user->getTokenExpiresAt() && $user->getTokenExpiresAt() > $now) {
            return $user->getApiToken();
        }

        $newToken = Uuid::v4()->toRfc4122();
        $user->setApiToken($newToken);
        $user->setTokenExpiresAt($now->modify('+14 days'));

        $this->entityManager->flush();

        return $newToken;
    }
}
