<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookAppropoController extends AbstractController
{
    private LoggerInterface $logger;
    private string $webhookSecret;

    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->logger = $logger;
        $this->webhookSecret = $params->get('APPROPO_WEBHOOK_SECRET');
    }

    #[Route('/api/appropo/order', name: 'webhook_appropo_order', methods: ['POST'])]
    public function receiveOrderWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('X-Signature');

        $this->logger->info('[Appropo] Webhook reçu', [
            'signature' => $signature,
            'content_type' => $request->headers->get('Content-Type'),
            'ip' => $request->getClientIp(),
            'payload_length' => strlen($payload),
        ]);

        if (!$this->validateSignature($payload, $signature)) {
            $this->logger->error('[Appropo] Signature invalide', [
                'signature_received' => $signature,
                'ip' => $request->getClientIp(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid signature',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('[Appropo] JSON invalide', [
                'error' => json_last_error_msg(),
                'payload' => substr($payload, 0, 500),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid JSON payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        $orderId = $data['order']['id'] ?? 'unknown';

        $this->logger->info('[Appropo] Commande reçue', [
            'order_id' => $orderId,
            'customer' => $data['customer']['email'] ?? 'N/A',
            'items_count' => count($data['items'] ?? []),
        ]);

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Order received',
        ], Response::HTTP_OK);
    }

    private function validateSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            $this->logger->warning('[Appropo] Aucune signature fournie');
            return false;
        }

        $signatureHash = str_replace('sha256=', '', $signature);
        $expectedHash = hash('sha256', $this->webhookSecret . $payload);

        return hash_equals($expectedHash, $signatureHash);
    }
}
