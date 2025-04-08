<?php

namespace App\Command;

use App\Service\Ovoko\ExportOvokoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportOvokoCommand extends Command
{
    protected static $defaultName = 'app:export-ovoko';

    private ExportOvokoService $exportOvokoService;

    public function __construct(ExportOvokoService $exportOvokoService)
    {
        parent::__construct();

        $this->setName(self::$defaultName);

        $this->exportOvokoService = $exportOvokoService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Exporte les données Ovoko dans un fichier CSV.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->exportOvokoService->exportDataToCsv();
            $output->writeln('<info>Export terminé avec succès.</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Erreur lors de l\'export : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}