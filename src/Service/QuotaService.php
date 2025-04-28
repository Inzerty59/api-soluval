<?php
// filepath: src/Service/QuotaService.php
namespace App\Service;

use Symfony\Component\RateLimiter\RateLimiterFactory;

class QuotaService
{
    private $minuteLimiter;
    private $hourLimiter;
    private $dayLimiter;

    public function __construct(RateLimiterFactory $minuteLimiter, RateLimiterFactory $hourLimiter, RateLimiterFactory $dayLimiter)
    {
        $this->minuteLimiter = $minuteLimiter->create();
        $this->hourLimiter = $hourLimiter->create();
        $this->dayLimiter = $dayLimiter->create();
    }

    public function getQuotas(): array
    {
        return [
            'CurrentMinuteQuota' => $this->minuteLimiter->consume(0)->getRemainingTokens(),
            'CurrentHourQuota' => $this->hourLimiter->consume(0)->getRemainingTokens(),
            'CurrentDayQuota' => $this->dayLimiter->consume(0)->getRemainingTokens(),
            'MaxMinuteQuota' => 500,
            'MaxHourQuota' => 5000,
            'MaxDayQuota' => 10000,
        ];
    }
}