<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpistoApiService
{
    private HttpClientInterface $httpClient;
    private AuthenticationService $authService;

    public function __construct(HttpClientInterface $httpClient, AuthenticationService $authService)
    {
        $this->httpClient = $httpClient;
        $this->authService = $authService;
    }

    public function getPartsDeletedBetween(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $token = $this->authService->getValidToken();
        $url = 'https://api.opisto.fr/v2.15/parts';
        $itemsPerPage = 100;
        $page = 0;
        $allParts = [];

        do {
            $params = [
                'startDeleteDate' => $start->format('d-m-Y-H-i-s'),
                'endDeleteDate' => $end->format('d-m-Y-H-i-s'),
                'itemsPerPage' => $itemsPerPage,
                'page' => $page,
                'onlyParts' => 'true',
            ];

            $response = $this->httpClient->request('GET', $url, [
                'query' => $params,
                'headers' => ['Token' => $token],
            ]);
            $data = $response->toArray();

            $parts = $data['Parts'] ?? [];
            $allParts = array_merge($allParts, $parts);

            $partsNumber = $data['PartsNumber'] ?? 0;
            $page++;
        } while (count($allParts) < $partsNumber);

        return $allParts;
    }

    public function getPartsCreatedBetween(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $token = $this->authService->getValidToken();
        $url = 'https://api.opisto.fr/v2.15/parts';
        $itemsPerPage = 100;
        $page = 0;
        $allParts = [];

        do {
            $params = [
                'startCreationDate' => $start->format('d-m-Y-H-i-s'),
                'endCreationDate' => $end->format('d-m-Y-H-i-s'),
                'itemsPerPage' => $itemsPerPage,
                'page' => $page,
                'onlyParts' => 'true',
            ];

            $response = $this->httpClient->request('GET', $url, [
                'query' => $params,
                'headers' => ['Token' => $token],
            ]);
            $data = $response->toArray();

            $parts = $data['Parts'] ?? [];
            $allParts = array_merge($allParts, $parts);

            $partsNumber = $data['PartsNumber'] ?? 0;
            $page++;
        } while (count($allParts) < $partsNumber);

        return $allParts;
    }

    public function getPartsModifiedBetween(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $token = $this->authService->getValidToken();
        $url = 'https://api.opisto.fr/v2.15/parts';
        $itemsPerPage = 100;
        $page = 0;
        $allParts = [];

        do {
            $params = [
                'startUpdateDate' => $start->format('d-m-Y-H-i-s'),
                'endUpdateDate' => $end->format('d-m-Y-H-i-s'),
                'itemsPerPage' => $itemsPerPage,
                'page' => $page,
                'onlyParts' => 'true',
            ];

            $response = $this->httpClient->request('GET', $url, [
                'query' => $params,
                'headers' => ['Token' => $token],
            ]);
            $data = $response->toArray();

            $parts = $data['Parts'] ?? [];
            $allParts = array_merge($allParts, $parts);

            $partsNumber = $data['PartsNumber'] ?? 0;
            $page++;
        } while (count($allParts) < $partsNumber);

        return $allParts;
    }
}