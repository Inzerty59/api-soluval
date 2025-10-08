<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookIntermobilitasController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/api/intermobilitas/order', name: 'webhook_intermobilitas_order', methods: ['POST'])]
    public function receiveOrderWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        
        $signature = $request->headers->get('X-Signature');
        $contentType = $request->headers->get('Content-Type');
        
        $this->logger->info('Webhook Intermobilitas reçu', [
            'signature' => $signature,
            'content_type' => $contentType,
            'payload' => $payload,
            'ip' => $request->getClientIp(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        $data = json_decode($payload, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->logger->info('Webhook Intermobilitas - Données décodées', [
                'action' => $data['action'] ?? 'N/A',
                'order_id' => $data['id'] ?? 'N/A',
                'data' => $data
            ]);
        } else {
            $this->logger->warning('Webhook Intermobilitas - JSON invalide', [
                'error' => json_last_error_msg()
            ]);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Webhook received and logged'
        ], Response::HTTP_OK);
    }
}
