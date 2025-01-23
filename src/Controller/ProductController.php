<?php

namespace App\Controller;

use App\Entity\Part;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/produit/{id}', name: 'product_detail')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer les informations du produit en fonction de l'ID
        $part = $entityManager->getRepository(Part::class)->find($id);

        if (!$part) {
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        // Passer les informations récupérées au template Twig
        return $this->render('product/detail.html.twig', [
            'part' => $part,
        ]);
    }
}