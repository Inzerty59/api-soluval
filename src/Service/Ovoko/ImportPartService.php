<?php

namespace App\Service\Ovoko;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\OvokoPartRepository;
use Psr\Log\LoggerInterface;

class ImportPartService
{
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private OvokoPartRepository $ovokoPartRepository;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        OvokoPartRepository $ovokoPartRepository,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->ovokoPartRepository = $ovokoPartRepository;
        $this->logger = $logger;
    }

    /**
     * Importe une pièce dans l'API Ovoko.
     *
     * @param int $carId
     * @param int $externalId
     * @param string $photo
     * @param array $photos
     * @param string $categoryName
     * @return int|null
     * @throws \Exception
     */
    public function importPart(
        int $carId,
        int $externalId,
        string $photo,
        array $photos,
        string $categoryName
    ): ?int {
        $url = $this->params->get('OVOKO_API_URL_IMPORT_PART');

        $username = $this->params->get('OVOKO_API_USERNAME');
        $password = $this->params->get('OVOKO_API_PASSWORD');
        $userToken = $this->params->get('OVOKO_API_USER_TOKEN');

        $categoryId = $this->ovokoPartRepository->findOvokoPartIdByCategoryName($categoryName);
        if ($categoryId === null) {
            throw new \Exception("Catégorie introuvable pour le nom : $categoryName");
        }

        $formData = [
            'username' => $username,
            'password' => $password,
            'user_token' => $userToken,
            'category_id' => $categoryId,
            'car_id' => $carId,
            'quality' => 0,
            'status' => 0,
            'external_id' => $externalId,
            'price' => null, // TODO
            'photo' => $photo,
            'photos' => $photos,
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => $formData,
            ]);

            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            if ($statusCode !== 200) {
                $this->logger->error('Erreur HTTP lors de l\'importation de la pièce', [
                    'status_code' => $statusCode,
                    'response' => $data,
                    'form_data' => $formData,
                ]);
                throw new \Exception("Erreur lors de l'importation de la pièce : Code HTTP $statusCode");
            }

            if (isset($data['status_code']) && $data['status_code'] === 'R200') {
                return $data['part_id'] ?? null;
            }

            $this->logger->error('Erreur API lors de l\'importation de la pièce', [
                'response' => $data,
                'form_data' => $formData,
            ]);
            throw new \Exception("La pièce n'a pas pu être importée.");
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'importation de la pièce", [
                'error' => $e->getMessage(),
                'form_data' => $formData,
            ]);
            throw new \Exception("Erreur lors de l'importation de la pièce : " . $e->getMessage());
        }
    }
}