<?php

namespace App\Command;

use App\Service\Appropo\AppropoSftpUploader;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportAppropoUnstockCommand extends Command
{
    protected static $defaultName = 'app:export-appropo-unstock';

    private const REMOTE_DIR = 'hour';

    public function __construct(
        private Connection $connection,
        private AppropoSftpUploader $uploader,
        private string $projectDir,
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Dépose dans hour/ le fichier appropo-unstock-<horodatage>.csv des pièces vendues depuis le dernier envoi.')
            ->addOption('no-upload', null, InputOption::VALUE_NONE, 'Génère le fichier sans l\'envoyer ni marquer les pièces comme envoyées.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = $this->connection->fetchAllAssociative('SELECT id, external_id FROM appropo_unstock WHERE sent_at IS NULL ORDER BY id');
        if (!$rows) {
            $output->writeln('Aucune pièce vendue à signaler.');
            return Command::SUCCESS;
        }

        $dir = $this->projectDir . '/var/appropo';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $output->writeln('<error>Création de ' . $dir . ' impossible.</error>');
            return Command::FAILURE;
        }

        $name = 'appropo-unstock-' . time() . '.csv';
        $file = $dir . '/' . $name;
        $ids = array_unique(array_column($rows, 'external_id'));
        file_put_contents($file, "pre_id\n" . implode("\n", $ids) . "\n");
        $output->writeln(sprintf('<info>%d pièces vendues écrites dans %s</info>', count($ids), $file));

        if ($input->getOption('no-upload')) {
            return Command::SUCCESS;
        }

        try {
            $this->uploader->upload($file, self::REMOTE_DIR, $name);
        } catch (\Throwable $e) {
            // Lignes laissées en sent_at NULL : renvoyées au prochain passage.
            $output->writeln('<error>Erreur dépôt Appropo : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $this->connection->executeStatement(
            'UPDATE appropo_unstock SET sent_at = NOW() WHERE id IN (?)',
            [array_column($rows, 'id')],
            [Connection::PARAM_INT_ARRAY]
        );
        $output->writeln('<info>Déposé dans ' . self::REMOTE_DIR . '/' . $name . '</info>');

        return Command::SUCCESS;
    }
}
