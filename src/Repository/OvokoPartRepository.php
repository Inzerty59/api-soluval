<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class OvokoPartRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Récupère l'ovoko_part_id pour une catégorie donnée.
     *
     * @param string $categoryName
     * @return int|null
     */
    public function findOvokoPartIdByCategoryName(string $categoryName): ?int
    {
        $query = 'SELECT ovoko_part_id FROM ovoko_part WHERE opisto_category_name = :categoryName';
        $result = $this->connection->fetchAssociative($query, [
            'categoryName' => $categoryName,
        ]);

        return $result['ovoko_part_id'] ?? null;
    }
}