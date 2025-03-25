<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class OvokoCarRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Récupère l'ovoko_model_id pour un modèle donné.
     *
     * @param string $modelName
     * @return int|null
     */
    public function findOvokoModelIdByModelName(string $modelName): ?int
    {
        $query = 'SELECT ovoko_model_id FROM ovoko_car WHERE opisto_model_name = :modelName';
        $result = $this->connection->fetchAssociative($query, [
            'modelName' => $modelName,
        ]);

        return $result['ovoko_model_id'] ?? null;
    }
}