<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/paiement', name: 'payment_page')]
    public function paymentPage(): Response
    {
        return new Response('<html><body><h1>Veuillez procéder au paiement.</h1></body></html>');
    }
}