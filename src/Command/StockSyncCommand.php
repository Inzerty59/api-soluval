<?php

namespace App\Command;

use App\Service\StockSyncService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StockSyncCommand extends Command
{
    protected static $defaultName = 'app:stock:sync';

    private StockSyncService $syncService;

    public function __construct(StockSyncService $syncService)
    {
        parent::__construct();
        $this->setName(self::$defaultName);
        $this->syncService = $syncService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Synchronise le stock Opisto/Ovoko.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $start = $now->modify('-2 minutes');
        $end = $now;

        $this->syncService->sync($start, $end);

        $output->writeln('Synchronisation terminée.');
        return Command::SUCCESS;
    }
}