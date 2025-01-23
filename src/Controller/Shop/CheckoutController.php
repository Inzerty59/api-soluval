<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    #[Route('/paiement', name: 'checkout_page')]
    public function paymentPage(): Response
    {
        return $this->render('payment/checkout.html.twig', [
            'controller_name' => 'CheckoutController',
    ]);
    }
}
