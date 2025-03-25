<?php

namespace App\Service\Ovoko;

use App\Entity\Part;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\OvokoCarRepository;

class ImportCarService
{
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private OvokoCarRepository $ovokoCarRepository;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        OvokoCarRepository $ovokoCarRepository
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->ovokoCarRepository = $ovokoCarRepository;
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
                return $data['car_id'] ?? null;
            }

            throw new \Exception("Le modèle n'a pas pu être importé.");
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'importation du modèle : " . $e->getMessage());
        }
    }
}