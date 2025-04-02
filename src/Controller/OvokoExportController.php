<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class OvokoExportController extends AbstractController
{
    #[Route('/secure/ovoko-export', name: 'ovoko_export')]
    public function download(Request $request): BinaryFileResponse
    {
        $token = $request->query->get('token');
        $expectedToken = $_ENV['OVOKO_EXPORT_TOKEN'];

        if ($token !== $expectedToken) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/ovoko_export.csv';

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }

        return (new BinaryFileResponse($filePath))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'ovoko_export.csv');
    }
}
