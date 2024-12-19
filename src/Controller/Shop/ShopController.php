<?php

namespace App\Controller\Shop;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DataImporter;

class ShopController extends AbstractController
{
    #[Route('/', name: 'shop_index')]
    public function index(DataImporter $dataImporter): Response
        {
        // Récupération des données depuis le service
        $data = $dataImporter->fetchData();
       

        return $this->render('shop/shop.html.twig', ['allData' => $data]);

    }
}
