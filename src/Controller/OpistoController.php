<?php

namespace App\Controller;

use App\Service\OpistoDataFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OpistoController extends AbstractController
{
    private OpistoDataFetcher $opistoService;

    public function __construct(OpistoDataFetcher $opistoService)
    {
        $this->opistoService = $opistoService;
    }

    #[Route('/test/simple-opisto', name: 'test_simple_opisto')]
    public function testOpisto(): Response
    {
        try {
            // Exemple de filtre : récupérer les pièces pour une marque spécifique
            // $filters = ['brandName' => 'TOYOTA'];
            $data = $this->opistoService->fetchParts();

            return $this->render('payment/checkout.html.twig', [
                'opistoData' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->render('payment/checkout.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
