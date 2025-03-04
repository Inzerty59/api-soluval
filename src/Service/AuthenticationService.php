<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Psr\Log\LoggerInterface;

class AuthenticationService
{
    private $client;
    private $cache;
    private $authUrl;
    private $credentials;
    private $retryDelay;
    private $logger;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;

        $this->authUrl = $_ENV['AUTH_URL'];
        $this->credentials = [
            'CasseId' => $_ENV['CASSE_ID'],
            'Password' => $_ENV['PASSWORD'],
            'SecretId' => $_ENV['SECRET_ID'],
            'Username' => $_ENV['USERNAME'],
        ];

        $this->retryDelay = $_ENV['RETRY_DELAY'] ?? 5;
    }

    public function authenticate(): array
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                $response = $this->client->request('POST', $this->authUrl, [
                    'json' => $this->credentials,
                ]);

                return $response->toArray();
            } catch (\Exception $e) {
                $this->logger->error('Authentication attempt failed', [
                    'attempt' => $attempts + 1,
                    'error' => $e->getMessage(),
                ]);

                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                sleep($this->retryDelay);
            }
        }
    }

    public function getToken(): ?string
    {
        return $this->cache->get('auth_token', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            $data = $this->authenticate();

            $item->set([
                'token' => $data['AccessToken'],
                'expiration' => $data['Expiration'],
            ]);

            return $data['AccessToken'];
        });
    }

    public function getValidToken(): ?string
    {
        $tokenData = $this->cache->getItem('auth_token')->get();

        if ($tokenData && isset($tokenData['expiration'])) {
            $expiration = new \DateTime($tokenData['expiration']);
            $now = new \DateTime();

            if ($now < $expiration) {
                return $tokenData['token'];
            }
        }
        return $this->getToken();
    }
}
