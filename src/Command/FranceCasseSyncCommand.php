<?php

namespace App\Command;

use App\Service\FranceCasse\SftpCheckerService;
use App\Service\Opisto\GenericClientCreator;
use App\Service\Ovoko\OrderSyncService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:francecasse:sync',
    description: 'Synchronise les commandes France Casse avec Opisto'
)]
class FranceCasseSyncCommand extends Command
{
    public function __construct(
        private SftpCheckerService $sftp,
        private GenericClientCreator $clientCreator,
        private OrderSyncService $orderSync,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $processed = [];
        $successCount = 0;
        $errorCount   = 0;

        try {
            $files = $this->sftp->getJsonFiles();

            if (empty($files)) {
                if ($output->isVerbose()) {
                    $output->writeln('Aucun fichier JSON trouvé sur le SFTP.');
                }
                return Command::SUCCESS;
            }

            foreach ($files as $file) {
                try {
                    $json = $this->sftp->getFileContent($file);
                    $order = json_decode($json, true);

                    $clientId = $this->clientCreator->handleClient($order);
                    if (!$clientId) {
                        throw new \Exception('Client non trouvé ou non créé');
                    }

                    $payload = $this->orderSync->transformFranceCasseOrder($order, $clientId);

                    $token = $this->orderSync->getAuthToken();
                    $response = $this->orderSync->sendOrderToOpisto($payload, $token); 

                    $opistoOrderId = $response['order_id'] ?? null;
                    $paymentId = $response['payment_id'] ?? null;
                    if ($opistoOrderId && $paymentId && isset($order['TotalPriceWithoutShipping'])) {
                        $this->orderSync->updatePayment($opistoOrderId, $paymentId, $order['TotalPriceWithoutShipping'], $token);
                    }

                    $this->sftp->moveFile($file, 'processed');
                    $successCount++;
                    $processed[] = [$file, 'OK', 'Commande synchronisée'];
                } catch (\Throwable $e) {
                    $this->logger->error('Erreur traitement fichier', [
                        'file' => $file,
                        'error' => $e->getMessage(),
                    ]);
                    $this->sftp->moveFile($file, 'error');
                    $errorCount++;
                    $processed[] = [$file, 'Erreur', $e->getMessage()];
                }
            }

            if ($output->isVerbose()) {
                $output->writeln("\n=== Récapitulatif ===");
                $table = new Table($output);
                $table->setHeaders(['Fichier', 'Statut', 'Détail']);
                $table->setRows($processed);
                $table->render();

                $output->writeln("\nSuccès : $successCount");
                $output->writeln("Erreurs : $errorCount");
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->logger->error('Erreur générale FranceCasseSyncCommand', [
                'error' => $e->getMessage(),
            ]);
            if ($output->isVerbose()) {
                $output->writeln("Erreur générale : {$e->getMessage()}");
            }
            return Command::FAILURE;
        }
    }
}
