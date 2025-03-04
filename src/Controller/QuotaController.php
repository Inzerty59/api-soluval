<?php

namespace App\Controller;

use App\Service\QuotaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class QuotaController extends AbstractController
{
    private $quotaService;
    private $minuteLimiter;
    private $hourLimiter;
    private $dayLimiter;

    public function __construct(QuotaService $quotaService, RateLimiterFactory $apiMinuteLimiter, RateLimiterFactory $apiHourLimiter, RateLimiterFactory $apiDayLimiter)
    {
        $this->quotaService = $quotaService;
        $this->minuteLimiter = $apiMinuteLimiter->create();
        $this->hourLimiter = $apiHourLimiter->create();
        $this->dayLimiter = $apiDayLimiter->create();
    }

    #[Route('/api/quotas', name: 'api_quotas', methods: ['GET'])]
    public function getQuotas(): JsonResponse
    {
        $minuteLimit = $this->minuteLimiter->consume(0);
        $hourLimit = $this->hourLimiter->consume(0);
        $dayLimit = $this->dayLimiter->consume(0);

        $quotas = [
            'CurrentMinuteQuota' => 500 - $minuteLimit->getRemainingTokens(),
            'CurrentHourQuota' => 5000 - $hourLimit->getRemainingTokens(),
            'CurrentDayQuota' => 10000 - $dayLimit->getRemainingTokens(),
            'MaxMinuteQuota' => 500,
            'MaxHourQuota' => 5000,
            'MaxDayQuota' => 10000,
        ];

        return new JsonResponse($quotas);
    }
}