<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    #[Route(path: '/mes-informations', name: 'user_informations')]
    public function informations(UserInterface $user): Response
    {
        return $this->render('user/informations.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/mes-commandes', name: 'user_orders')]
    public function orders(UserInterface $user): Response
    {
        return $this->render('user/orders.html.twig', [
            'user' => $user,
        ]);
    }
}