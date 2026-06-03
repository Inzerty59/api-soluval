<?php

namespace App\Command;

use App\Service\StockSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[AsCommand(
    name: 'app:stock:sync',
    description: 'Synchronise le stock Opisto/Ovoko.'
)]
class StockSyncCommand extends Command
{
    private StockSyncService $syncService;
    private LoggerInterface $logger;

    public function __construct(StockSyncService $syncService, LoggerInterface $logger = null)
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
                'Période à synchroniser (ex: 2min, 1h, 3h, 6h, 12h, 24h, 3d, 7d, 14d)',
                '2min'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $period = $input->getOption('period') ?? '2min';
        $parisTz = new \DateTimeZone('Europe/Paris');
        $now = new \DateTimeImmutable('now', $parisTz);

        switch ($period) {
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
            case '3d':
                $start = $now->modify('-3 days');
                break;
            case '7d':
                $start = $now->modify('-7 days');
                break;
            case '14d':
                $start = $now->modify('-14 days');
                break;
            case '2min':
            default:
                $start = $now->modify('-2 minutes');
                break;
        }
        $end = $now;

        $maxAttempts = 3;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxAttempts && !$success) {
            $attempt++;
            try {
                $this->logger->info("Tentative $attempt/$maxAttempts : Synchronisation du stock pour la période $period.");
                $output->writeln("Tentative $attempt/$maxAttempts : Synchronisation du stock pour la période $period.");
                $this->syncService->sync($start, $end);
                $this->logger->info("Synchronisation réussie pour la période $period.");
                $output->writeln("Synchronisation réussie pour la période $period.");
                $success = true;
            } catch (\Throwable $e) {
                $this->logger->error("Erreur lors de la synchronisation (tentative $attempt/$maxAttempts, période $period) : " . $e->getMessage());
                $output->writeln("<error>Erreur lors de la synchronisation (tentative $attempt/$maxAttempts, période $period) : " . $e->getMessage() . "</error>");
                if ($attempt < $maxAttempts) {
                    sleep(2); 
                }
            }
        }

        if (!$success) {
            $this->logger->error("Echec de la synchronisation après $maxAttempts tentatives pour la période $period.");
            $output->writeln("<error>Echec de la synchronisation après $maxAttempts tentatives pour la période $period.</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
