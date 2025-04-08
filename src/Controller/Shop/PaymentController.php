<?php

namespace App\Controller\Shop;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    #[Route('/payment/{orderNumber}', name: 'create_checkout_session')]
    public function createCheckoutSession(Request $request, string $orderNumber): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $totalAmount = $request->query->get('totalAmount');

        if (!$totalAmount) {
            return new JsonResponse(['error' => 'Invalid order data'], Response::HTTP_BAD_REQUEST);
        }

        $session = Session::create([
            'payment_method_types' => ['card'], 
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande n°' . $orderNumber,
                    ],
                    'unit_amount' => $totalAmount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', ['orderNumber' => $orderNumber], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', ['orderNumber' => $orderNumber], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/payment/{orderNumber}/success', name: 'payment_success')]
    public function paymentSuccess(string $orderNumber): Response
    {
        return $this->render('order/success.html.twig', ['orderNumber' => $orderNumber]);
    }

    #[Route('/payment/{orderNumber}/cancel', name: 'payment_cancel')]
    public function paymentCancel(string $orderNumber): Response
    {
        return $this->render('order/cancel.html.twig', ['orderNumber' => $orderNumber]);
    }
}