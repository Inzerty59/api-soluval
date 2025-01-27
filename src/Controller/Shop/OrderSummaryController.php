<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderSummaryController extends AbstractController
{
    #[Route('/recapulatif', name: 'orderSummary_page')]
    public function paymentPage(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        $total = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['part']->getFinalPrice() * ($item['quantity'] ?? 1));
        }, 0);

        $totalHT = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['part']->getPriceHT() * ($item['quantity'] ?? 1));
        }, 0);

        $totalVAT = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['part']->getEstimatedVAT() * ($item['quantity'] ?? 1));
        }, 0);

        return $this->render('payment/orderSummary.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'totalHT' => $totalHT,
            'totalVAT' => $totalVAT,
        ]);
    }
}
