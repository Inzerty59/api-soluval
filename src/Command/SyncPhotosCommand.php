<?php

namespace App\Command;

use App\Service\OpistoApiService;
use App\Service\PhotoSyncService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync-photos',
    description: 'Synchronise les photos des pièces modifiées sur OPISTO'
)]
class SyncPhotosCommand extends Command
{
    private OpistoApiService $opistoApi;
    private PhotoSyncService $photoSync;
    private LoggerInterface $logger;

    public function __construct(
        OpistoApiService $opistoApi,
        PhotoSyncService $photoSync,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->opistoApi = $opistoApi;
        $this->photoSync = $photoSync;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronise les photos des pièces modifiées')
            ->addOption(
                'period',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Intervalle de temps à synchroniser (ex: 15min, 1h, 6h, 1day, 1week, 3months, 1year)',
                '15min'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $period = $input->getOption('period') ?? '15min';
            $parisTz = new \DateTimeZone('Europe/Paris');
            $now = new \DateTimeImmutable('now', $parisTz);

            // Calculer l'intervalle
            $start = $this->calculateStartDate($now, $period);

            $output->writeln("<info>Synchronisation des photos modifiées entre {$start->format('Y-m-d H:i:s')} et {$now->format('Y-m-d H:i:s')}</info>");
            $this->logger->info("Début sync photos - Période: $period");

            // Récupérer les pièces modifiées
            $modifiedParts = $this->opistoApi->getPartsModifiedBetween($start, $now);
            $output->writeln("<info>Nombre de pièces modifiées trouvées: " . count($modifiedParts) . "</info>");

            if (empty($modifiedParts)) {
                $output->writeln("<comment>Aucune pièce modifiée trouvée</comment>");
                return Command::SUCCESS;
            }

            // Traiter chaque pièce
            $syncedCount = 0;
            $skippedCount = 0;
            $notFoundCount = 0;

            foreach ($modifiedParts as $part) {
                try {
                    $isSynced = $this->photoSync->syncPhotosForPart($part);
                    if ($isSynced) {
                        $syncedCount++;
                    } else {
                        // Pièce pas en base ou pas dispo
                        $notFoundCount++;
                    }
                } catch (\Exception $e) {
                    $skippedCount++;
                    $this->logger->warning("Erreur sync photos pour pièce {$part['Id']}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $output->writeln("<info>✅ Synchronisation terminée</info>");
            $output->writeln("<info>Pièces synchronisées: $syncedCount</info>");
            $output->writeln("<comment>Pièces non trouvées/non disponibles: $notFoundCount</comment>");
            if ($skippedCount > 0) {
                $output->writeln("<error>Pièces en erreur: $skippedCount</error>");
            }

            $this->logger->info("Fin sync photos", [
                'synced' => $syncedCount,
                'not_found' => $notFoundCount,
                'skipped' => $skippedCount,
                'period' => $period,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Erreur: {$e->getMessage()}</error>");
            $this->logger->error("Erreur sync photos", ['exception' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    /**
     * Calcule la date de début selon la période
     */
    private function calculateStartDate(\DateTimeImmutable $now, string $period): \DateTimeImmutable
    {
        // Parser la période (ex: "15min", "1h", "3months")
        if (preg_match('/^(\d+)(\w+)$/', $period, $matches)) {
            $value = (int) $matches[1];
            $unit = $matches[2];
        } else {
            throw new \InvalidArgumentException("Format de période invalide. Utilisez: 15min, 1h, 1day, 1week, 3months, etc.");
        }

        return match ($unit) {
            'min' => $now->modify("-$value minutes"),
            'h' => $now->modify("-$value hours"),
            'hour' => $now->modify("-$value hours"),
            'day' => $now->modify("-$value days"),
            'week' => $now->modify("-$value weeks"),
            'month', 'months' => $now->modify("-$value months"),
            'year' => $now->modify("-$value years"),
            default => throw new \InvalidArgumentException("Unité de temps inconnue: $unit"),
        };
    }
}
