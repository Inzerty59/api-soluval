<?php

namespace App\Controller\Shop;

use App\Entity\DeliveryAdress;
use App\Entity\BillingAdress;
use App\Entity\Shippings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderSummaryController extends AbstractController
{
    #[Route('/recapitulatif', name: 'orderSummary_page')]
    public function paymentPage(SessionInterface $session, EntityManagerInterface $entityManager, Request $request): Response
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

        $deliveryAdressId = $request->query->get('deliveryAdress');
        $billingAdressId = $request->query->get('billingAdress');
        $deliveryMode = $request->query->get('delivery_mode');

        $deliveryAdress = $deliveryAdressId ? $entityManager->getRepository(DeliveryAdress::class)->find($deliveryAdressId) : null;
        $billingAdress = $entityManager->getRepository(BillingAdress::class)->find($billingAdressId);

        $shippingCosts = ['HT' => 0, 'TTC' => 0];
        $deliveryMinTime = null;
        $deliveryMaxTime = null;

        if ($deliveryAdress) {
            $shipping = $entityManager->getRepository(Shippings::class)->findOneBy(['CountryId' => $deliveryAdress->getCountryId()]);
            if ($shipping) {
                $shippingCosts = $shipping->getShippingCosts();
                $deliveryMinTime = $shipping->getDelayMin();
                $deliveryMaxTime = $shipping->getDelayMax();
            }
        }

        return $this->render('payment/orderSummary.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'totalHT' => $totalHT,
            'totalVAT' => $totalVAT,
            'deliveryAdress' => $deliveryAdress,
            'billingAdress' => $billingAdress,
            'shippingCosts' => $shippingCosts,
            'deliveryMode' => $deliveryMode,
            'deliveryMinTime' => $deliveryMinTime,
            'deliveryMaxTime' => $deliveryMaxTime,
        ]);
    }
}
