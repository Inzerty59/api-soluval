<?php
namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Entity\Part;
class OpistoStockChecker
{
    private $client;
    private $authService;
    private $authUrlApi;
    private $entityManager;
    private $logger;
    public function __construct(HttpClientInterface $client, AuthenticationService $authService, string $authUrlApi, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->authService = $authService;
        $this->authUrlApi = $authUrlApi;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    public function checkStock(int $externalId): bool
    {
        try {
            $token = $this->authService->getValidToken();
            $response = $this->client->request('GET', $this->authUrlApi . '/parts/' . $externalId, [
                'headers' => [
                    'Token' => $token,
                ],
            ]);
            $data = $response->getContent();

            $dataArray = json_decode($data, true);
            if ($dataArray['Available'] === true) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification du stock', ['exception' => $e]);
            return false;
        }
    }
}