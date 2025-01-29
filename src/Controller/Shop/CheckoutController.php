<?php

namespace App\Controller\Shop;

use App\Entity\DeliveryAdress;
use App\Entity\BillingAdress;
use App\Form\DeliveryAdressType;
use App\Form\BillingAdressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class CheckoutController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/paiement', name: 'checkout_page')]
    public function checkout(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $cart = $session->get('cart', []);

        $total = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['part']->getFinalPrice() * ($item['quantity'] ?? 1));
        }, 0);

        $deliveryAdress = new DeliveryAdress();
        $billingAdress = new BillingAdress();

        $deliveryForm = $this->createForm(DeliveryAdressType::class, $deliveryAdress);
        $billingForm = $this->createForm(BillingAdressType::class, $billingAdress);

        $deliveryForm->handleRequest($request);
        $billingForm->handleRequest($request);

        if ($deliveryForm->isSubmitted() && $deliveryForm->isValid() && $billingForm->isSubmitted() && $billingForm->isValid()) {
            $user = $this->security->getUser();
            if ($user) {
                $deliveryAdress->setEmail($user->getEmail());
                $billingAdress->setEmail($user->getEmail());
            }

            $entityManager->persist($deliveryAdress);
            $entityManager->persist($billingAdress);
            $entityManager->flush();

            return $this->redirectToRoute('orderSummary_page',[
                'deliveryAdress' => $deliveryAdress->getId(),
                'billingAdress' => $billingAdress->getId(),
            ]);
        }

        return $this->render('payment/checkout.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'deliveryForm' => $deliveryForm->createView(),
            'billingForm' => $billingForm->createView(),
        ]);
    }
}
