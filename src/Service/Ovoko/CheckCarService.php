<?php

namespace App\Service\Ovoko;

use App\Entity\Part;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class CheckCarService
{
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $params;
    private ImportCarService $importCarService;
    private LoggerInterface $logger;

    public function __construct(
        ImportCarService $importCarService,
        HttpClientInterface $httpClient,
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->importCarService = $importCarService;
        $this->httpClient = $httpClient;
        $this->params = $params;
        $this->logger = $logger;
    }

    /**
     *
     * @param Part $part
     * @return array|null
     */
    public function checkPart(Part $part): ?array
    {
        $brandId = $this->checkBrandExists($part->getBrandName());

        if ($brandId === null) {
            return null;
        }

        $modelName = $part->getModelName();
        if (is_null($modelName) || trim($modelName) === '') {
            $this->logger->warning('Modèle null ou vide détecté, passage à l\'élément suivant.', [
                'external_id' => $part->getExternalId(),
            ]);
            return null;
        }

        $modelId = $this->checkModelExists($brandId, $modelName);

        if ($modelId === null) {
            $carId = $this->importCarService->importCarModel($part, $brandId);

            return [
                'brand_id' => $brandId,
                'car_id' => $carId,
            ];
        }

        return [
            'brand_id' => $brandId,
            'car_id' => $modelId,
        ];
    }

    /**
     * Récupère toutes les marques de véhicules depuis l'API Ovoko.
     *
     * @return array
     * @throws \Exception
     */
    public function getCarBrands(): array
    {
        $url = $this->params->get('OVOKO_API_URL_BRAND');

        $formData = [
            'username' => $this->params->get('OVOKO_API_USERNAME'),
            'password' => $this->params->get('OVOKO_API_PASSWORD'),
            'user_token' => $this->params->get('OVOKO_API_USER_TOKEN'),
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => $formData,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception("Erreur lors de la récupération des marques : Code HTTP $statusCode");
            }

            $data = $response->toArray();

            return $data['list'] ?? [];
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la récupération des marques : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si une marque existe dans les données récupérées.
     *
     * @param string $brandName
     * @return int|null
     */
    public function checkBrandExists(string $brandName): ?int
    {
        $carBrands = $this->getCarBrands();

        foreach ($carBrands as $brand) {
            if (strcasecmp($brand['name'], $brandName) === 0) {
                return (int) $brand['id'];
            }
        }

        return null;
    }

    /**
     * Récupère tous les modèles d'une marque spécifique.
     *
     * @param int $brandId
     * @return array
     * @throws \Exception
     */
    public function getCarModels(int $brandId): array
    {
        $url = $this->params->get('OVOKO_API_URL_MODEL') . "/{$brandId}";

        $formData = [
            'username' => $this->params->get('OVOKO_API_USERNAME'),
            'password' => $this->params->get('OVOKO_API_PASSWORD'),
            'user_token' => $this->params->get('OVOKO_API_USER_TOKEN'),
        ];

        $this->logger->info('Requête envoyée à l\'API Ovoko pour récupérer les modèles', [
            'url' => $url,
            'form_data' => $formData,
        ]);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'body' => $formData,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception("Erreur lors de la récupération des modèles : Code HTTP $statusCode");
            }

            $data = $response->toArray();

            $this->logger->info('Réponse de l\'API Ovoko pour les modèles', ['response' => $data]);

            return $data['list'] ?? [];
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des modèles', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Vérifie si un modèle existe pour une marque spécifique.
     *
     * @param int $brandId
     * @param string $modelName
     * @return int|null
     */
    public function checkModelExists(int $brandId, string $modelName): ?int
    {
        $carModels = $this->getCarModels($brandId);

        foreach ($carModels as $model) {
            if (strcasecmp($model['name'], $modelName) === 0) {
                $this->logger->info('Modèle correspondant trouvé', ['model_id' => $model['id']]);
                return (int) $model['id'];
            }
        }

        $this->logger->warning('Aucun modèle correspondant trouvé', ['part_model_name' => $modelName]);
        return null;
    }
}