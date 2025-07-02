<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class OvokoExportRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Récupère les données pour l'export Ovoko.
     *
     * @return array
     */
    public function getExportData(): array
    {
        $query = <<<SQL
SELECT 
    p.external_id AS part_external_id, 
    MAX(op.ovoko_part_id) AS part_ovoko_category_id, 
    TRIM(BOTH ',' FROM REPLACE(
        CONCAT(
            MAX(p.vignette), 
            IF(
                MAX(p.photos) IS NOT NULL AND MAX(p.photos) != '', 
                CONCAT(',', REPLACE(
                    REGEXP_REPLACE(
                        MAX(p.photos), 
                        'a:[0-9]+:\\\\{|i:[0-9]+;s:[0-9]+:|["{};]', 
                        ''
                    ), 
                    'https', ',https'
                )), 
                ''
            )
        ), 
        ',,', ','
    )) AS part_photo_urls, 
    REPLACE(
        FORMAT(
            ROUND(
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(MAX(p.price), 's:11:"OriginPrice";d:', -1), ';', 1) AS DECIMAL(10,2)) * 
                (1 + CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(MAX(p.price), 's:7:"VATRate";d:', -1), ';', 1) AS DECIMAL(10,2)) / 100), 
                0
            ), 
            2
        ), 
        ',', ''
    ) AS part_price, 
    MAX(p.manufacturer_reference) AS part_manufacturer_code, 
    1 AS part_quality, 
    1 AS part_status, 
    MAX(oc.ovoko_model_id) AS car_ovoko_model_id, 
    MAX(p.vehicle_year) AS car_year,
    p.category_name,
    CASE
        WHEN LOWER(p.category_name) LIKE '%gauche%' AND LOWER(p.category_name) LIKE '%arriere%' THEN 'left_rear'
        WHEN LOWER(p.category_name) LIKE '%droit%' AND LOWER(p.category_name) LIKE '%arriere%' THEN 'right_rear'
        WHEN LOWER(p.category_name) LIKE '%gauche%' AND LOWER(p.category_name) LIKE '%avant%' THEN 'left_front'
        WHEN LOWER(p.category_name) LIKE '%droit%' AND LOWER(p.category_name) LIKE '%avant%' THEN 'right_front'
        WHEN LOWER(p.category_name) LIKE '%gauche%' THEN 'left'
        WHEN LOWER(p.category_name) LIKE '%droit%' THEN 'right'
        WHEN LOWER(p.category_name) LIKE '%centre%' OR LOWER(p.category_name) LIKE '%central%' THEN 'center'
        WHEN LOWER(p.category_name) LIKE '%ensemble%' OR LOWER(p.category_name) LIKE '%jeu%' OR LOWER(p.category_name) LIKE '%set%' THEN 'set'
        WHEN LOWER(p.category_name) LIKE '%avant%' THEN 'in_front'
        WHEN LOWER(p.category_name) LIKE '%arriere%' THEN 'rear'
        ELSE 'all'
    END AS part_position
FROM part p 
LEFT JOIN ovoko_part op ON op.opisto_category_name = p.category_name 
LEFT JOIN ovoko_car oc ON oc.opisto_model_name = p.model_name
WHERE op.ovoko_part_id NOT IN ('Opisto one option in Ovoko more options', 'no category on Ovoko')
  AND p.available = 1
  AND p.vehicle_year IS NOT NULL
  AND p.shipping_id IS NOT NULL
GROUP BY p.external_id, p.category_name;

SQL;

        return $this->connection->fetchAllAssociative($query);
    }
}