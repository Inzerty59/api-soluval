<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSummaryController extends AbstractController
{
    #[Route('/recapulatif', name: 'orderSummary_page')]
    public function paymentPage(): Response
    {
        return $this->render('payment/orderSummary.html.twig', [
            'controller_name' => 'OrdersummaryController',
    ]);
    }
}
