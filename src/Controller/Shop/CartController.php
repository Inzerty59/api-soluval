<?php

namespace App\Controller\Shop;

use App\Entity\Part;
use App\Service\OpistoStockChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/panier', name: 'cart_')]
class CartController extends AbstractController
{
    private $stockChecker;

    public function __construct(OpistoStockChecker $stockChecker)
    {
        $this->stockChecker = $stockChecker;
    }

    // Afficher le contenu du panier
    #[Route('/', name: 'view')]
    public function view(SessionInterface $session): Response
    {
        // Récupérer le panier de la session
        $cart = $session->get('cart', []);

        // Nettoyer le panier pour ne garder que les produits valides
        $cart = array_filter($cart, function ($item) {
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
    public function add(int $id, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Récupérer le produit correspondant à l'ID depuis la base de données
        $product = $entityManager->getRepository(Part::class)->find($id);

        // Si aucun produit n'est trouvé, rediriger avec un message d'erreur
        if (!$product) {
            $this->addFlash('error', "Produit avec l'ID $id introuvable.");
            return $this->redirectToRoute('cart_view');
        }

        // Récupérer le panier de la session
        $cart = $session->get('cart', []);

        // Vérifier si le produit est déjà dans le panier
        foreach ($cart as $item) {
            if ($item['part']->getId() === $id) {
                $this->addFlash('error', "Le produit est déjà dans le panier.");
                return $this->redirectToRoute('cart_view');
            }
        }

        // Vérifier la disponibilité de la pièce via l'API externe
        $isAvailable = $this->stockChecker->checkStock($product->getExternalId());
        if (!$isAvailable) {
            $this->addFlash('error', "La pièce {$product->getName()} n'est plus disponible.");
            return $this->redirectToRoute('cart_view');
        }

        // Ajouter le produit au panier
        $cart[] = ['part' => $product];
        $session->set('cart', $cart);

        // Rediriger vers la vue du panier avec un message de succès
        $this->addFlash('success', "Produit ajouté au panier.");
        return $this->redirectToRoute('cart_view');
    }

    #[Route('/mettre-a-jour-panier/{id}', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request, SessionInterface $session): JsonResponse
    {
        // Logique pour mettre à jour le panier
        return new JsonResponse(['success' => true]);
    }

    #[Route('/supprimer-du-panier/{id}', name: 'delete')]
    public function delete(int $id, SessionInterface $session): Response
    {
        // Récupérer le panier de la session
        $cart = $session->get('cart', []);

        // Filtrer le panier pour supprimer le produit avec l'ID donné
        $cart = array_filter($cart, function ($item) use ($id) {
            return isset($item['part']) && $item['part']->getId() !== $id;
        });

        // Mettre à jour le panier dans la session
        $session->set('cart', $cart);

        // Ajouter un message flash pour indiquer que le produit a été supprimé
        $this->addFlash('success', "Produit supprimé du panier.");

        // Rediriger vers la vue du panier
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
}