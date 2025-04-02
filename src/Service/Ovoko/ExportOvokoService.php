<?php

namespace App\Service\Ovoko;

use App\Repository\OvokoExportRepository;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;

class ExportOvokoService
{
    private OvokoExportRepository $repository;
    private Filesystem $filesystem;
    private LoggerInterface $logger;

    public function __construct(OvokoExportRepository $repository, Filesystem $filesystem, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     *
     * @throws \Exception
     */
    public function exportDataToCsv(): void
    {
        ini_set('memory_limit', '-1');

        try {
            $data = $this->repository->getExportData();

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