<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart_index')]
    public function index(): Response
        {
        
       

        return $this->render('cart/cart.html.twig');

    }
}
