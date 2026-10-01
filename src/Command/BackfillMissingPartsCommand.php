<?php

namespace App\Command;

use App\Service\OpistoApiService;
use App\Service\PartPersistenceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:parts:backfill',
    description: 'Importe les pièces disponibles chez Opisto qui sont absentes de la base (n\'écrase aucune pièce existante).'
)]
class BackfillMissingPartsCommand extends Command
{
    public function __construct(
        private OpistoApiService $opistoApi,
        private PartPersistenceService $persistenceService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Compte les pièces manquantes sans rien écrire')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Nombre maximum de pièces à importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

        $dryRun = $input->getOption('dry-run');
        $limit = $input->getOption('limit') !== null ? (int) $input->getOption('limit') : null;

        $existing = array_flip(array_map('intval',
            $this->entityManager->getConnection()->fetchFirstColumn('SELECT external_id FROM part')
        ));
        $output->writeln(count($existing) . ' pièces déjà en base.');

        $missing = $imported = $errors = $pages = 0;

        foreach ($this->opistoApi->iterateAllPartsPages() as $parts) {
            $pages++;
            foreach ($parts as $part) {
                $id = (int) ($part['Id'] ?? 0);
                if (!$id || isset($existing[$id]) || ($part['Available'] ?? false) !== true) {
                    continue;
                }
                $missing++;

                if ($dryRun) {
                    continue;
                }

                try {
                    $this->persistenceService->persistPart($part);
                    $imported++;
                } catch (\Throwable $e) {
                    $errors++;
                    $output->writeln("<error>Pièce $id : {$e->getMessage()}</error>");
                    // ponytail: un échec Doctrine ferme l'EntityManager; on stoppe, relancer reprend (les pièces déjà importées sont ignorées)
                    if (!$this->entityManager->isOpen()) {
                        $output->writeln('<error>EntityManager fermé, relancer la commande.</error>');
                        return Command::FAILURE;
                    }
                }

                if ($limit !== null && $imported >= $limit) {
                    break 2;
                }
            }

            $this->entityManager->clear();
            if ($pages % 50 === 0) {
                $output->writeln("Page $pages : $missing manquantes, $imported importées, $errors erreurs");
            }
        }

        $output->writeln(sprintf(
            '%s : %d manquantes, %d importées, %d erreurs.',
            $dryRun ? 'Simulation' : 'Terminé',
            $missing,
            $imported,
            $errors
        ));

        return $errors ? Command::FAILURE : Command::SUCCESS;
    }
}
