<?php

namespace App\Controller\Shop;

use App\Entity\DeliveryAdress;
use App\Entity\BillingAdress;
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

        $deliveryAdress = $entityManager->getRepository(DeliveryAdress::class)->find($deliveryAdressId);
        $billingAdress = $entityManager->getRepository(BillingAdress::class)->find($billingAdressId);

        $shippingCosts = $this->getShippingCosts($deliveryAdress->getCountryName());
        
        return $this->render('payment/orderSummary.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'totalHT' => $totalHT,
            'totalVAT' => $totalVAT,
            'deliveryAdress' => $deliveryAdress,
            'billingAdress' => $billingAdress,
            'shippingCosts' => $shippingCosts,
        ]);
    }

    private function getShippingCosts(string $country): array
    {
        $shippingCosts = [
            'France' => ['TTC' => 17.00, 'HT' => 14.17],
            'Belgique' => ['TTC' => 17.00, 'HT' => 14.17],
            'Espagne' => ['TTC' => 17.00, 'HT' => 14.17],
            'Italie' => ['TTC' => 17.00, 'HT' => 14.17],
            'Luxembourg' => ['TTC' => 17.00, 'HT' => 14.17],
            'Portugal' => ['TTC' => 17.00, 'HT' => 14.17],
            'Suisse' => ['TTC' => 14.17, 'HT' => 14.17],
            'Allemagne' => ['TTC' => 17.00, 'HT' => 14.17],
            'Guadeloupe' => ['TTC' => 14.17, 'HT' => 14.17],
            'Martinique' => ['TTC' => 14.17, 'HT' => 14.17],
            'Ile de la Réunion' => ['TTC' => 14.17, 'HT' => 14.17],
            'Corse' => ['TTC' => 17.00, 'HT' => 14.17],
            'Mayotte' => ['TTC' => 14.17, 'HT' => 14.17],
            'Polynésie Française' => ['TTC' => 14.17, 'HT' => 14.17],
            'Saint-Barthélemy' => ['TTC' => 14.17, 'HT' => 14.17],
            'Saint-Martin' => ['TTC' => 14.17, 'HT' => 14.17],
            'Saint-Pierre-et-Miquelon' => ['TTC' => 14.17, 'HT' => 14.17],
            'Wallis et Futuna' => ['TTC' => 14.17, 'HT' => 14.17],
            'Pays-Bas' => ['TTC' => 17.00, 'HT' => 14.17],
            'Guyane Française' => ['TTC' => 14.17, 'HT' => 14.17],
            'Monaco' => ['TTC' => 14.17, 'HT' => 14.17],
            'Autriche' => ['TTC' => 17.00, 'HT' => 14.17],
            'Norvège' => ['TTC' => 14.17, 'HT' => 14.17],
            'Suède' => ['TTC' => 17.00, 'HT' => 14.17],
            'Finlande' => ['TTC' => 17.00, 'HT' => 14.17],
            'Danemark' => ['TTC' => 17.00, 'HT' => 14.17],
            'Grèce' => ['TTC' => 17.00, 'HT' => 14.17],
            'Maroc' => ['TTC' => 14.17, 'HT' => 14.17],
            'Algérie' => ['TTC' => 14.17, 'HT' => 14.17],
        ];
        return $shippingCosts[$country] ?? ['TTC' => 0, 'HT' => 0];
    }
}
