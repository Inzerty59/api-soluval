<?php

namespace App\Controller\Shop;

use App\Entity\DeliveryAdress;
use App\Entity\BillingAdress;
use App\Entity\Shippings;
use App\Form\DeliveryAdressType;
use App\Form\BillingAdressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CheckoutController extends AbstractController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
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

        if ($billingForm->isSubmitted() && $billingForm->isValid()) {
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user) {
                $billingAdress->setEmail($user->getEmail());
                $billingAdress->setUser($user);
            }

            $deliveryMode = $request->request->get('delivery_mode');
            $session->set('delivery_mode', $deliveryMode);

            if ($deliveryMode === 'comptoir') {
                $entityManager->persist($billingAdress);
                $entityManager->flush();

                return $this->redirectToRoute('orderSummary_page', [
                    'billingAdress' => $billingAdress->getId(),
                    'delivery_mode' => $deliveryMode,
                ]);
            } elseif ($deliveryForm->isSubmitted() && $deliveryForm->isValid()) {
                if ($user) {
                    $deliveryAdress->setEmail($user->getEmail());
                    $deliveryAdress->setUser($user);
                }

                $deliveryShipping = $deliveryForm->get('shipping')->getData();
                $billingShipping = $billingForm->get('shipping')->getData();

                if ($deliveryShipping) {
                    $deliveryAdress->setShipping($deliveryShipping);
                } else {
                    throw new \Exception('Delivery shipping not found');
                }

                if ($billingShipping) {
                    $billingAdress->setShipping($billingShipping);
                } else {
                    throw new \Exception('Billing shipping not found');
                }

                $entityManager->persist($deliveryAdress);
                $entityManager->persist($billingAdress);
                $entityManager->flush();

                return $this->redirectToRoute('orderSummary_page', [
                    'billingAdress' => $billingAdress->getId(),
                    'deliveryAdress' => $deliveryAdress->getId(),
                    'delivery_mode' => $deliveryMode,
                ]);
            }
        }

        return $this->render('payment/checkout.html.twig', [
            'cart' => $cart,
            'total' => $total,
            'deliveryForm' => $deliveryForm->createView(),
            'billingForm' => $billingForm->createView(),
        ]);
    }
}
