<?php

namespace App\Controller;

use App\Service\GlobalPre\GlobalPreOrderService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookGlobalPreController extends AbstractController
{
    private LoggerInterface $logger;
    private GlobalPreOrderService $orderService;
    private string $webhookSecret;

    public function __construct(
        LoggerInterface $logger,
        GlobalPreOrderService $orderService,
        ParameterBagInterface $params
    ) {
        $this->logger = $logger;
        $this->orderService = $orderService;
        $this->webhookSecret = $params->get('GLOBALPRE_WEBHOOK_SECRET');
    }

    #[Route('/api/globalpre/order', name: 'webhook_globalpre_order', methods: ['POST'])]
    public function receiveOrderWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('X-Signature');

        $this->logger->info('[GlobalPre] Webhook reçu', [
            'signature' => $signature,
            'content_type' => $request->headers->get('Content-Type'),
            'ip' => $request->getClientIp(),
            'timestamp' => date('Y-m-d H:i:s'),
            'payload_length' => strlen($payload)
        ]);

        if (!$this->validateSignature($payload, $signature)) {
            $this->logger->error('[GlobalPre] Signature invalide', [
                'signature_received' => $signature,
                'ip' => $request->getClientIp(),
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid signature'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->logger->info('[GlobalPre] Signature validée avec succès');

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('[GlobalPre] JSON invalide', [
                'error' => json_last_error_msg(),
                'payload' => substr($payload, 0, 500)
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid JSON payload'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->orderService->processOrder($data);

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Order processed successfully'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('[GlobalPre] Erreur lors du traitement de la commande', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'status' => 'error',
                'message' => 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateSignature(string $payload, ?string $signature): bool
    {
        if (!$signature) {
            $this->logger->warning('[GlobalPre] Aucune signature fournie');
            return false;
        }

        $signatureHash = str_replace('sha256=', '', $signature);
        $expectedHash = hash('sha256', $this->webhookSecret . $payload);

        return hash_equals($expectedHash, $signatureHash);
    }
}
