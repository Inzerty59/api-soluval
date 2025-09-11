<?php

namespace App\Command;

use App\Service\CreationSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[AsCommand(
    name: 'app:creation:sync',
    description: 'Synchronise les nouvelles pièces créées dans Opisto.'
)]
class CreationSyncCommand extends Command
{
    private CreationSyncService $syncService;
    private LoggerInterface $logger;

    public function __construct(CreationSyncService $syncService, LoggerInterface $logger = null)
    {
        parent::__construct();
        $this->syncService = $syncService;
        $this->logger = $logger ?? new NullLogger();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'period',
                null,
                InputOption::VALUE_OPTIONAL,
                'Période à synchroniser (ex: 5min, 15min, 30min, 1h, 3h, 6h, 12h, 24h)',
                '15min'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Limite du nombre de pièces à traiter (sécurité)',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');

        $period = $input->getOption('period') ?? '15min';
        $parisTz = new \DateTimeZone('Europe/Paris');
        $now = new \DateTimeImmutable('now', $parisTz);

        switch ($period) {
            case '5min':
                $start = $now->modify('-5 minutes');
                break;
            case '15min':
                $start = $now->modify('-15 minutes');
                break;
            case '30min':
                $start = $now->modify('-30 minutes');
                break;
            case '1h':
                $start = $now->modify('-1 hour');
                break;
            case '3h':
                $start = $now->modify('-3 hours');
                break;
            case '6h':
                $start = $now->modify('-6 hours');
                break;
            case '12h':
                $start = $now->modify('-12 hours');
                break;
            case '24h':
                $start = $now->modify('-24 hours');
                break;
            default:
                $start = $now->modify('-5 minutes');
                break;
        }
        $end = $now;

        $maxAttempts = 3;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxAttempts && !$success) {
            $attempt++;
            try {
                $this->logger->info("Tentative $attempt/$maxAttempts : Synchronisation des nouvelles pièces pour la période $period.");
                $output->writeln("Tentative $attempt/$maxAttempts : Synchronisation des nouvelles pièces pour la période $period.");
                
                $limit = $input->getOption('limit');
                if ($limit !== null) {
                    $this->logger->info("Limite de traitement définie : $limit pièces");
                    $output->writeln("Limite de traitement définie : $limit pièces");
                }
                
                $this->syncService->sync($start, $end);
                $this->logger->info("Synchronisation des nouvelles pièces réussie pour la période $period.");
                $output->writeln("Synchronisation des nouvelles pièces réussie pour la période $period.");
                $success = true;
            } catch (\Throwable $e) {
                $this->logger->error("Erreur lors de la synchronisation des nouvelles pièces (tentative $attempt/$maxAttempts, période $period) : " . $e->getMessage());
                $output->writeln("<error>Erreur lors de la synchronisation des nouvelles pièces (tentative $attempt/$maxAttempts, période $period) : " . $e->getMessage() . "</error>");
                if ($attempt < $maxAttempts) {
                    sleep(2); 
                }
            }
        }

        if (!$success) {
            $this->logger->error("Echec de la synchronisation des nouvelles pièces après $maxAttempts tentatives pour la période $period.");
            $output->writeln("<error>Echec de la synchronisation des nouvelles pièces après $maxAttempts tentatives pour la période $period.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
