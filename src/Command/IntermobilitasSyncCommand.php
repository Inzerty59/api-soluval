<?php

namespace App\Command;

use App\Service\Intermobilitas\IntermobilitasSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:intermobilitas:sync',
    description: 'Synchronizes parts to TotalParts/Intermobilitas API',
)]
class IntermobilitasSyncCommand extends Command
{
    private IntermobilitasSyncService $syncService;

    public function __construct(IntermobilitasSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    protected function configure(): void
    {
        $this->addOption(
            'delete-unavailable',
            'd',
            InputOption::VALUE_NONE,
            'Also delete unavailable parts from TotalParts'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('TotalParts/Intermobilitas Synchronization');

        $io->section('Synchronizing available parts');

        try {
            $stats = $this->syncService->syncAllParts();

            $io->table(
                ['Metric', 'Value'],
                [
                    ['Total parts', $stats['total']],
                    ['Successful', $stats['success']],
                    ['Errors', $stats['errors']],
                    ['Skipped', $stats['skipped']],
                ]
            );

            if ($stats['success'] > 0) {
                $io->success(sprintf('Successfully synchronized %d part(s)', $stats['success']));
            }

            if ($stats['errors'] > 0) {
                $io->warning(sprintf('%d error(s) occurred. Check logs for details.', $stats['errors']));
            }
        } catch (\Exception $e) {
            $io->error('Synchronization failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($input->getOption('delete-unavailable')) {
            $io->section('Deleting unavailable parts');

            try {
                $deleteStats = $this->syncService->deleteUnavailableParts();

                $io->table(
                    ['Metric', 'Value'],
                    [
                        ['Total deletions', $deleteStats['total']],
                        ['Successful', $deleteStats['success']],
                        ['Errors', $deleteStats['errors']],
                    ]
                );

                if ($deleteStats['success'] > 0) {
                    $io->success(sprintf('Successfully deleted %d part(s)', $deleteStats['success']));
                }
            } catch (\Exception $e) {
                $io->error('Deletion failed: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $io->success('Synchronization completed successfully');
        return Command::SUCCESS;
    }
}
