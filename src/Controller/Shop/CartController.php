<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DataImporter;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'cart_index')]
    public function index(SessionInterface $session, DataImporter $dataImporter): Response
    {
        $cart = $session->get('cart', []);
        $allProducts = $dataImporter->fetchData()['products'];
        $cartWithDetails = [];

        foreach ($cart as $id => $quantity) {
            foreach ($allProducts as $product) {
                if ($product['part']['Id'] === $id) {
                    $cartWithDetails[] = [
                        'product' => $product['part'],
                        'quantity' => $quantity,
                    ];
                    break;
                }
            }
        }

        return $this->render('cart/cart.html.twig', [
            'cart' => $cartWithDetails,
        ]);
    }

    #[Route('/ajouter-au-panier/{id}', name: 'cart_add')]
    public function addToCart($id, SessionInterface $session, Request $request): Response
    {
        $cart = $session->get('cart', []);
        

        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $session->set('cart', $cart);

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('shop_index'));
    }

    #[Route('/retirer-du-panier/{id}', name: 'cart_remove')]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }
}
