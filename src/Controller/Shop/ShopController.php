<?php

namespace App\Controller\Shop;

use App\Entity\Part;
use App\Repository\PartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_index')]
    public function index(PartRepository $partRepository): Response
    {
        $parts = $partRepository->findAll();

        return $this->render('shop/shop.html.twig', [
            'parts' => $parts,
        ]);
    }

    #[Route('/shop/search', name: 'shop_search', methods: ['GET'])]
    public function search(Request $request, PartRepository $partRepository): Response
    {
        $query = $request->query->get('query');
        $brand = $request->query->get('brand');
        $model = $request->query->get('model');
        $sort = $request->query->get('sort');
        $parts = $partRepository->searchParts($query, $brand, $model, $sort);

        return $this->json($parts, 200, [], [AbstractNormalizer::GROUPS => ['part:read']]);
    }

    #[Route('/product/{id}', name: 'product_detail', methods: ['GET'])]
    public function productDetail(int $id, PartRepository $partRepository): Response
    {
        $part = $partRepository->find($id);

        if (!$part) {
            throw $this->createNotFoundException('La pièce demandée n\'existe pas.');
        }

        return $this->render('product/detail.html.twig', [
            'part' => $part,
        ]);
    }

    #[Route('/shop/brands', name: 'shop_brands', methods: ['GET'])]
    public function getBrands(PartRepository $partRepository): Response
    {
        $brands = $partRepository->findDistinctBrands();

        return $this->json($brands);
    }

    #[Route('/shop/models', name: 'shop_models', methods: ['GET'])]
    public function getModels(PartRepository $partRepository): Response
    {
        $models = $partRepository->findDistinctModels();

        return $this->json($models);
    }
}
