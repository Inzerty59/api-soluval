<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PartsController extends AbstractController
{
    #[Route('/api/parts', name: 'api_parts', methods: ['GET'])]
    public function getParts(UserInterface $user): JsonResponse
    {
        $parts = [
            ['id' => 1, 'name' => 'Part 1', 'description' => 'Description of Part 1'],
            ['id' => 2, 'name' => 'Part 2', 'description' => 'Description of Part 2'],
        ];

        return $this->json([
            'user' => $user->getEmail(),
            'parts' => $parts,
        ]);
    }
}