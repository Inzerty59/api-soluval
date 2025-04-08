<?php

namespace App\Service\Ovoko;

use App\Entity\Part;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\OvokoCarRepository;
use Psr\Log\LoggerInterface;

class ImportCarService
{
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private OvokoCarRepository $ovokoCarRepository;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        OvokoCarRepository $ovokoCarRepository,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->ovokoCarRepository = $ovokoCarRepository;
        $this->logger = $logger;
    }

    /**
     * Importe un modèle de voiture dans l'API Ovoko.
     *
     * @param Part $part
     * @param int $brandId
     * @return int|null
     * @throws \Exception
     */
    public function importCarModel(Part $part, int $brandId): ?int
    {
        $url = $this->params->get('OVOKO_API_URL_IMPORT_CAR');

        $carModelId = $this->ovokoCarRepository->findOvokoModelIdByModelName($part->getModelName());

        if (!$carModelId) {
            $this->logger->warning('Modèle de voiture introuvable', [
                'model_name' => $part->getModelName(),
                'brand_id' => $brandId,
            ]);
            return null;
        }

        $formData = [
            'username' => $this->params->get('OVOKO_API_USERNAME'),
            'password' => $this->params->get('OVOKO_API_PASSWORD'),
            'user_token' => $this->params->get('OVOKO_API_USER_TOKEN'),
            'car_model' => $carModelId,
            'car_years' => $part->getVehicleYear(),
            'status' => 6,
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => $formData,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception("Erreur lors de l'importation du modèle : Code HTTP $statusCode");
            }

            $data = $response->toArray();

            if (isset($data['status_code']) && $data['status_code'] === 'R200') {
                // Log des informations sur la voiture importée
                $this->logger->info('Voiture importée avec succès', [
                    'car_id' => $data['car_id'] ?? null,
                    'model_name' => $part->getModelName(),
                    'brand_id' => $brandId,
                    'car_years' => $part->getVehicleYear(),
                ]);

                return $data['car_id'] ?? null;
            }

            throw new \Exception("Le modèle n'a pas pu être importé.");
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'importation du modèle', [
                'model_name' => $part->getModelName(),
                'brand_id' => $brandId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Erreur lors de l'importation du modèle : " . $e->getMessage());
        }
    }
}