<?php

namespace App\Command;

use App\Service\OpistoApiService;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:parts:backfill-vehicle-fields',
    description: 'Repasse les pièces déjà en base pour renseigner type_mine/code_couleur/police_id (ajoutés après coup ou jamais écrits avant le fix setPoliceId, absents des anciennes pièces).'
)]
class BackfillVehicleFieldsCommand extends Command
{
    public function __construct(
        private OpistoApiService $opistoApi,
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Compte les pièces à mettre à jour sans rien écrire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);

        $dryRun = $input->getOption('dry-run');

        // ponytail: UPDATE direct en SQL, pas d'hydratation Doctrine — un flush par pièce via
        // persistPart serait beaucoup trop lent sur ~71k lignes (vu sur le backfill précédent)
        $existing = array_flip(array_map('intval',
            $this->connection->fetchFirstColumn('SELECT external_id FROM part')
        ));
        $output->writeln(count($existing) . ' pièces en base.');

        $stmt = $this->connection->prepare(
            'UPDATE part SET type_mine = ?, code_couleur = ?, police_id = ? WHERE external_id = ?'
        );

        $updated = $pages = 0;

        foreach ($this->opistoApi->iterateAllPartsPages() as $parts) {
            $pages++;
            foreach ($parts as $part) {
                $id = (int) ($part['Id'] ?? 0);
                if (!$id || !isset($existing[$id])) {
                    continue;
                }

                $typeMine = $part['Vehicle']['TypeMine'] ?? null;
                $codeCouleur = $part['Vehicle']['CodeCouleur'] ?? null;
                $policeId = $part['Vehicle']['PoliceId'] ?? null;
                if ($typeMine === null && $codeCouleur === null && $policeId === null) {
                    continue;
                }

                $updated++;
                if (!$dryRun) {
                    $stmt->executeStatement([$typeMine, $codeCouleur, $policeId, $id]);
                }
            }

            if ($pages % 50 === 0) {
                $output->writeln("Page $pages : $updated mises à jour");
            }
        }

        $output->writeln(($dryRun ? 'Simulation' : 'Terminé') . " : $updated pièces mises à jour.");

        return Command::SUCCESS;
    }
}
