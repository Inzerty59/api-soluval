<?php

namespace App\Controller\Shop;

use App\Entity\DeliveryAdress;
use App\Entity\BillingAdress;
use App\Entity\Shippings;
use App\Entity\Order;
use App\Entity\Part;
use App\Service\OpistoStockChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrderSummaryController extends AbstractController
{
    private $stockChecker;
    private $params;

    public function __construct(OpistoStockChecker $stockChecker, ParameterBagInterface $params)
    {
        $this->stockChecker = $stockChecker;
        $this->params = $params;
    }

    #[Route('/recapitulatif', name: 'orderSummary_page')]
    public function paymentPage(SessionInterface $session, EntityManagerInterface $entityManager, Request $request): Response
    {
        $cart = $session->get('cart', []);

        $cart = array_filter($cart, function ($item) {
            if (!$this->stockChecker->checkStock($item['part']->getExternalId())) {
                $this->addFlash('error', "La pièce {$item['part']->getName()} n'est plus disponible.");
                return false;
            }
            return true;
        });

        $session->set('cart', $cart);

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

        $session->set('billingAdress', $billingAdressId);
        $session->set('deliveryAdress', $deliveryAdressId);

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

    #[Route('/finaliser-commande', name: 'finalize_order', methods: ['POST'])]
    public function finalizeOrder(SessionInterface $session, EntityManagerInterface $entityManager, OpistoStockChecker $stockChecker): Response
    {
        $cart = $session->get('cart', []);

        $billingAdressId = $session->get('billingAdress');
        $deliveryMode = $session->get('delivery_mode');

        foreach ($cart as $item) {
            if (!$stockChecker->checkStock($item['part']->getExternalId())) {
                $this->addFlash('error', "La pièce {$item['part']->getName()} n'est plus disponible.");
                return $this->redirectToRoute('orderSummary_page');
            }
        }

        if (!$billingAdressId) {
            $this->addFlash('error', 'L\'adresse de facturation est manquante.');
            return $this->redirectToRoute('orderSummary_page');
        }

        $billingAdress = $entityManager->getRepository(BillingAdress::class)->find($billingAdressId);
        if (!$billingAdress) {
            $this->addFlash('error', 'L\'adresse de facturation est invalide.');
            return $this->redirectToRoute('orderSummary_page');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setBillingAdress($billingAdress);

        if ($deliveryMode !== 'comptoir') {
            $deliveryAdressId = $session->get('deliveryAdress');
            if ($deliveryAdressId) {
                $deliveryAdress = $entityManager->getRepository(DeliveryAdress::class)->find($deliveryAdressId);
                if ($deliveryAdress) {
                    $order->setDeliveryAdress($deliveryAdress);
                }
            }
        }

        $order->setToSend($deliveryMode !== 'comptoir');
        $order->setFreeShipping($deliveryMode === 'comptoir');
        $order->setOrderNumber(uniqid());

        foreach ($cart as $item) {
            $part = $entityManager->getRepository(Part::class)->find($item['part']->getId());
            if ($part) {
                $order->addPart($part);
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $session->remove('cart');

        return $this->redirectToRoute('order_confirmation', ['orderNumber' => $order->getOrderNumber()]);
    }

    #[Route('/orders', name: 'order_confirmation')]
    public function orderConfirmation(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $orderNumber = $request->query->get('orderNumber');
        $order = $entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            throw $this->createNotFoundException('Order not found');
        }

        $billingAdress = $order->getBillingAdress();
        $deliveryAdress = $order->getDeliveryAdress();
        $deliveryMode = $session->get('delivery_mode', 'comptoir');

        $netToPay = $order->getNetToPay();

        return $this->render('order/confirmation.html.twig', [
            'order' => $order,
            'billingAdress' => $billingAdress,
            'deliveryAdress' => $deliveryAdress,
            'deliveryMode' => $deliveryMode,
            'netToPay' => $netToPay,
        ]);
    }

    #[Route('/order/{orderNumber}/json', name: 'order_json')]
    public function getOrderJson(string $orderNumber, EntityManagerInterface $entityManager): JsonResponse
    {
        $order = $entityManager->getRepository(Order::class)->findOneBy(['orderNumber' => $orderNumber]);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        $user = $order->getUser();
        $billingAddress = $order->getBillingAdress();
        $deliveryAddress = $order->getDeliveryAdress();
        $parts = $order->getParts();

        $billingCountry = $entityManager->getRepository(Shippings::class)->findOneBy(['CountryId' => $billingAddress->getCountryId()]);
        $billingCountryName = $billingCountry ? $billingCountry->getTitle() : 'FRANCE';
        $billingCountryISOCode = $billingCountry ? $billingCountry->getISOCode() : 'FR';

        $deliveryCountry = $deliveryAddress ? $entityManager->getRepository(Shippings::class)->findOneBy(['CountryId' => $deliveryAddress->getCountryId()]) : null;
        $deliveryCountryName = $deliveryCountry ? $deliveryCountry->getTitle() : 'FRANCE';
        $deliveryCountryISOCode = $deliveryCountry ? $deliveryCountry->getISOCode() : 'FR';

        $casseId = count($parts) > 0 ? $parts[0]->getCasseId() : null;
        $origin = count($parts) > 0 ? $parts[0]->getOrigin() : null;

        $response = [
            'BillingAddress' => [
                'City' => $billingAddress->getCity(),
                'Country' => [
                    'Name' => $billingCountryName,
                    'ISOCode' => $billingCountryISOCode,
                ],
                'Email' => $billingAddress->getEmail(),
                'Firstname' => $billingAddress->getFirstname(),
                'Lastname' => $billingAddress->getLastname(),
                'Phone1' => $billingAddress->getPhone(),
                'PostCode' => $billingAddress->getPostCode(),
                'Street' => $billingAddress->getStreet(),
                'StreetAdditionnal' => $billingAddress->getStreetAdditionnal(),
            ],
            'DeliveryAddress' => $deliveryAddress ? [
                'City' => $deliveryAddress->getCity(),
                'Country' => [
                    'Name' => $deliveryCountryName,
                    'ISOCode' => $deliveryCountryISOCode,
                ],
                'Email' => $deliveryAddress->getEmail(),
                'Firstname' => $deliveryAddress->getFirstname(),
                'Lastname' => $deliveryAddress->getLastname(),
                'Phone1' => $deliveryAddress->getPhone(),
                'PostCode' => $deliveryAddress->getPostCode(),
                'Street' => $deliveryAddress->getStreet(),
                'StreetAdditionnal' => $deliveryAddress->getStreetAdditionnal(),
            ] : null,
            'CasseId' => $casseId, // TODO
            'ClientId' => $user->getId(),
            'Comment' => null,
            'Origin' => $origin,
            'ShippingStatus' => $order->isFreeShipping(),
            'Status' => $order->getStatus(), // TODO 
            'ToSend' => $order->isToSend(),
            'Parts' => array_map(function($part) {
                return [
                    'Key' => $part->getExternalId(),                
                    'Value' => 0, // TODO
                ];
            }, $parts->toArray()),
        ];

        return new JsonResponse($response);
    }
}