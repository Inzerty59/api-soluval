<?php

namespace App\Service\Ovoko;

use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

class ExportOvokoService
{
    private Connection $connection;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, Filesystem $filesystem, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * Exécute la requête SQL et exporte les données dans un fichier CSV.
     *
     * @throws \Exception
     */
    public function exportDataToCsv(): void
    {
        ini_set('memory_limit', '-1');
        $query = <<<SQL
SELECT 
    p.external_id AS part_external_id, 
    op.ovoko_part_id AS part_ovoko_id, 
    TRIM(BOTH ',' FROM REPLACE(
        CONCAT(
            p.vignette, 
            IF(
                p.photos IS NOT NULL AND p.photos != '', 
                CONCAT(',', REPLACE(
                    REGEXP_REPLACE(
                        p.photos, 
                        '(a:\\d+:\\{|i:\\d+;s:\\d+:|"|\\}|\\{|;)', 
                        ''
                    ), 
                    'https', ',https'
                )), 
                ''
            )
        ), 
        ',,', ','
    )) AS part_photo_urls, 
    TRUNCATE(
        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p.price, 's:11:"OriginPrice";d:', -1), ';', 1) AS DECIMAL(10,2)) * 
        (1 + CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p.price, 's:7:"VATRate";d:', -1), ';', 1) AS DECIMAL(10,2)) / 100), 
        2 
    ) AS part_price, 
    p.manufacturer_reference AS part_manufacturer_code, 
    1 AS part_quality, 
    1 AS part_status, 
    oc.ovoko_model_id AS car_ovoko_model 
FROM part p 
LEFT JOIN ovoko_part op ON op.opisto_category_name = p.category_name 
LEFT JOIN ovoko_car oc ON oc.opisto_model_name = p.model_name;
SQL;

        try {
            $data = $this->connection->fetchAllAssociative($query);

            if (empty($data)) {
                $this->logger->warning('Aucune donnée trouvée pour l\'export.');
                return;
            }

            $csvFilePath = 'public/uploads/ovoko_export.csv';

            $csvFile = fopen($csvFilePath, 'w');
            if ($csvFile === false) {
                throw new \Exception('Impossible de créer le fichier CSV.');
            }

            fputcsv($csvFile, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($csvFile, $row);
            }

            fclose($csvFile);

            $this->logger->info('Export des données terminé avec succès.', [
                'file_path' => $csvFilePath,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'export des données.', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}