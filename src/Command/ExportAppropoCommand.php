<?php

namespace App\Command;

use App\Service\Appropo\AppropoSftpUploader;
use App\Service\Appropo\ExportAppropoService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportAppropoCommand extends Command
{
    protected static $defaultName = 'app:export-appropo';

    private const REMOTE_DIR = 'day';
    private const REMOTE_NAME = 'appropo-pieces.csv';

    public function __construct(
        private ExportAppropoService $exportService,
        private AppropoSftpUploader $uploader,
        private string $projectDir,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Génère le catalogue Appropo (CSV 21 colonnes) et le dépose dans day/ sur leur SFTP.')
            ->addOption('no-upload', null, InputOption::VALUE_NONE, 'Génère le fichier sans l\'envoyer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        $dir = $this->projectDir . '/var/appropo';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $output->writeln('<error>Création de ' . $dir . ' impossible.</error>');
            return Command::FAILURE;
        }
        $file = $dir . '/' . self::REMOTE_NAME;

        try {
            $result = $this->exportService->export($file);
            $output->writeln(sprintf('<info>%d pièces écrites dans %s</info>', $result['written'], $file));
            foreach ($result['skipped'] as $reason => $count) {
                $output->writeln(sprintf('  ignorées (%s) : %d', $reason, $count));
            }

            // Fichier vide = l'export remplacerait tout le stock Appropo par rien.
            if ($result['written'] === 0) {
                $output->writeln('<error>Aucune pièce exportable, dépôt annulé.</error>');
                return Command::FAILURE;
            }

            if ($input->getOption('no-upload')) {
                return Command::SUCCESS;
            }

            $this->uploader->upload($file, self::REMOTE_DIR, self::REMOTE_NAME);
            $output->writeln('<info>Déposé dans ' . self::REMOTE_DIR . '/' . self::REMOTE_NAME . '</info>');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Erreur export Appropo : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
