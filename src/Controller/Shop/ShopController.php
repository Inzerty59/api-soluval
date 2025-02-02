<?php

namespace App\Controller\Shop;

use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les données de l'entité Part
        $parts = $entityManager->getRepository(Part::class)->findAll();

        // Passer les données au template Twig
        return $this->render('shop/shop.html.twig', [
            'parts' => $parts,
        ]);
    }
}
