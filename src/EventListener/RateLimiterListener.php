<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimiterListener
{
    private $minuteLimiter;
    private $hourLimiter;
    private $dayLimiter;

    public function __construct(RateLimiterFactory $apiMinuteLimiter, RateLimiterFactory $apiHourLimiter, RateLimiterFactory $apiDayLimiter)
    {
        $this->minuteLimiter = $apiMinuteLimiter->create();
        $this->hourLimiter = $apiHourLimiter->create();
        $this->dayLimiter = $apiDayLimiter->create();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (strpos($request->getPathInfo(), '/api/parts') === 0) {
            $minuteLimit = $this->minuteLimiter->consume(1);
            $hourLimit = $this->hourLimiter->consume(1);
            $dayLimit = $this->dayLimiter->consume(1);

            if (!$minuteLimit->isAccepted() || !$hourLimit->isAccepted() || !$dayLimit->isAccepted()) {
                throw new TooManyRequestsHttpException();
            }
        }
    }
}