<?php

namespace App\Service\Opisto;

use App\Service\AuthenticationService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GenericClientCreator
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private AuthenticationService $authService,
        private LoggerInterface $logger
    ) {}

    public function handleClient(array $data): ?int
    {
        $email =
            $data['client_email']
            ?? ($data['BillingAddress']['Email'] ?? null)
            ?? ($data['DeliveryAddress']['Email'] ?? null);

        if (!$email) {
            $this->logger->warning("Aucun email trouvé dans les données JSON", ['data' => $data]);
            return null;
        }

        try {
            $token = $this->authService->getValidToken();

            $urlCheck = "https://api.opisto.fr/v2.15/clients?email=" . urlencode($email);
            $checkResponse = $this->httpClient->request('GET', $urlCheck, [
                'headers' => ['Token' => $token]
            ]);
            $checkData = $checkResponse->toArray(false);

            if (!empty($checkData['Clients'])) {
                $clientId = $checkData['Clients'][0]['Id'] ?? null;
                $this->logger->info("Client trouvé", ['email' => $email, 'client_id' => $clientId]);
                return $clientId;
            }

            if (!empty($data['client_name'])) {
                $parts = explode(' ', trim($data['client_name']));
                $firstname = $parts[0] ?? '';
                $lastname  = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';
            } else {
                $firstname =
                    $data['BillingAddress']['Firstname']
                    ?? $data['DeliveryAddress']['Firstname']
                    ?? '';
                $lastname  =
                    $data['BillingAddress']['Lastname']
                    ?? $data['DeliveryAddress']['Lastname']
                    ?? '';
            }

            $createResponse = $this->httpClient->request('POST', 'https://api.opisto.fr/v2.15/clients', [
                'headers' => ['Token' => $token],
                'json' => [
                    'Email'     => $email,
                    'Firstname' => $firstname,
                    'Lastname'  => $lastname,
                ]
            ]);
            $createData = $createResponse->toArray(false);

            if (!empty($createData['ObjectCreated']) && $createData['ObjectCreated'] === true) {
                $clientId = $createData['ObjectIdCreated'] ?? null;
                $this->logger->info("Client créé avec succès", ['email' => $email, 'client_id' => $clientId]);
                return $clientId;
            }

            $this->logger->error("Échec création client", [
                'email' => $email,
                'response' => $createData
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de la gestion client", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
