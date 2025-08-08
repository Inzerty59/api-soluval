<?php

namespace App\Command;

use App\Service\FranceCasse\SftpCheckerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:check-sftp', description: 'Vérifie les fichiers JSON sur le serveur SFTP')]
class CheckSftpCommand extends Command
{
    private SftpCheckerService $sftpCheckerService;

    public function __construct(SftpCheckerService $sftpCheckerService)
    {
        parent::__construct();
        $this->sftpCheckerService = $sftpCheckerService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $files = $this->sftpCheckerService->getJsonFiles();
            $output->writeln('Fichiers JSON trouvés :');
            foreach ($files as $file) {
                $output->writeln($file);
            }
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}