<?php
namespace App\Controller;

use App\Service\AuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SomeController extends AbstractController
{
    private $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @Route("/some-route", name="some_route")
     */
    public function someAction(): Response
    {
        $token = $this->authService->getValidToken();

        // Utilisez le token pour d'autres requêtes ou traitements
        // ...

        return new Response('Token: ' . $token);
    }
}


// EXEMPLE POUR 2.6