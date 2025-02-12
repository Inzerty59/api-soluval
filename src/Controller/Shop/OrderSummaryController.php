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

        $response = [
            'OrderNumber' => $order->getOrderNumber(),
            'ClientId' => $user->getId(),
            'Status' => $order->getStatus(),
            'ToSend' => $order->isToSend(),
            'IsFreeShipping' => $order->isFreeShipping(),
            'Parts' => array_map(function($part) {
                return [
                    'Id' => $part->getExternalId(),                
                ];
            }, $parts->toArray()),
            'BillingAddress' => [
                'Firstname' => $billingAddress->getFirstname(),
                'Lastname' => $billingAddress->getLastname(),
                'Phone' => $billingAddress->getPhone(),
                'Street' => $billingAddress->getStreet(),
                'PostCode' => $billingAddress->getPostCode(),
                'City' => $billingAddress->getCity(),
                'CountryId' => $billingAddress->getCountryId(),
                'Email' => $billingAddress->getEmail(),
            ],
        ];

        if ($deliveryAddress) {
            $response['DeliveryAddress'] = [
                'Firstname' => $deliveryAddress->getFirstname(),
                'Lastname' => $deliveryAddress->getLastname(),
                'Phone' => $deliveryAddress->getPhone(),
                'Street' => $deliveryAddress->getStreet(),
                'PostCode' => $deliveryAddress->getPostCode(),
                'City' => $deliveryAddress->getCity(),
                'CountryId' => $deliveryAddress->getCountryId(),
                'Email' => $deliveryAddress->getEmail(),
            ];
        }

        $client = new Client();
        $apiUrl = $this->params->get('API_ORDER_URL');
        $responseApi = $client->post($apiUrl, [
            'json' => $response
        ]);

        if ($responseApi->getStatusCode() !== 200) {
            return new JsonResponse(['error' => 'Failed to send order to external API'], 500);
        }

        return new JsonResponse($response);
    }
}