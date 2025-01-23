<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\DataImporter;

#[Route('/panier', name: 'cart_')]
class CartController extends AbstractController
{
    // Afficher le contenu du panier
    #[Route('/', name: 'view')]
    public function view(SessionInterface $session): Response
    {
        // Récupérer et filtrer le panier
        $cart = array_filter($session->get('cart', []), function ($item) {
            return is_array($item) && isset($item['part']);
        });
    
        // Mettre à jour le panier propre dans la session
        $session->set('cart', $cart);
    
        // Rendre la vue avec le panier nettoyé
        return $this->render('cart/cart.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/ajouter-au-panier/{id}', name: 'add')]
    public function add(int $id, DataImporter $dataImporter, SessionInterface $session): Response
    {
        // Récupérer les données disponibles
        $data = $dataImporter->fetchData();
    
        // Trouver le produit correspondant à l'ID
        $productFound = null;
        foreach ($data['products'] as $product) {
            if ((int) $product['part']['Id'] === $id) {
                $productFound = $product;
                break;
            }
        }
    
        // Si aucun produit n'est trouvé, rediriger avec un message d'erreur
        if (!$productFound) {
            $this->addFlash('error', "Produit avec l'ID $id introuvable.");
            return $this->redirectToRoute('cart_view');
        }
    
        // Ajouter uniquement des produits valides
        $cart = $session->get('cart', []);
        if (is_array($productFound) && isset($productFound['part'])) {
            $cart[] = $productFound;
            $session->set('cart', $cart);
            $this->addFlash('success', "Produit ajouté au panier.");
        } else {
            $this->addFlash('error', "Le produit est invalide.");
        }
    
        return $this->redirectToRoute('cart_view');
    }

    #[Route('/mettre-a-jour-panier/{id}', name: 'update', methods: ['POST'])]
public function update(int $id, Request $request, SessionInterface $session): JsonResponse
{
    $cart = $session->get('cart', []);

    // Récupérer les données envoyées par AJAX
    $data = json_decode($request->getContent(), true);
    $quantity = max(1, (int)($data['quantity'] ?? 1)); // Empêche les quantités < 1

    // Mettre à jour la quantité
    foreach ($cart as &$item) {
        if (isset($item['part']['Id']) && (int) $item['part']['Id'] === $id) {
            $item['quantity'] = $quantity;
            break;
        }
    }

    // Sauvegarder le panier dans la session
    $session->set('cart', $cart);

    // Retourner une réponse JSON
    return new JsonResponse(['success' => true]);
}


    #[Route('/supprimer-du-panier/{id}', name: 'delete')]
public function delete(int $id, SessionInterface $session): Response
{
    // Récupérer le panier de la session
    $cart = $session->get('cart', []);

    // Filtrer le panier pour retirer le produit avec l'ID correspondant
    $cart = array_filter($cart, function ($item) use ($id) {
        return !(isset($item['part']['Id']) && (int)$item['part']['Id'] === $id);
    });

    // Réindexer le tableau après suppression
    $cart = array_values($cart);

    // Mettre à jour le panier dans la session
    $session->set('cart', $cart);

    // Ajouter un message de confirmation
    $this->addFlash('success', "Le produit a été supprimé du panier.");

    // Rediriger vers la page du panier
    return $this->redirectToRoute('cart_view');
}

#[Route('/finaliser-commande', name: 'checkout')]
public function checkout(): Response
{
    if (!$this->getUser()) {
        return $this->redirectToRoute('app_login');
    }
    return $this->redirectToRoute('checkout_page');
}

#[Route('/produit/{id}', name: 'product_detail')]
public function productDetail(int $id, DataImporter $dataImporter): Response
{
    $data = $dataImporter->fetchData();

    $productFound = null;
    foreach ($data['products'] as $product) {
        if ((int) $product['part']['Id'] === $id) {
            $productFound = $product;
            break;
        }
    }

    if (!$productFound) {
        $this->addFlash('error', "Produit avec l'ID $id introuvable.");
        return $this->redirectToRoute('shop_index');
    }
    return $this->render('product/detail.html.twig', [
        'product' => $productFound,
    ]);
}
}