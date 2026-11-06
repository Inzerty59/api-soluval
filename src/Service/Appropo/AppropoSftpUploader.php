<?php

namespace App\Service\Appropo;

use Psr\Log\LoggerInterface;

class AppropoSftpUploader
{
    public function __construct(
        private string $host,
        private int $port,
        private string $username,
        private string $password,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Dépose un fichier local dans un dossier distant (ex. "day"), sous le nom $remoteName.
     * Envoi vers un fichier temporaire puis renommage, pour qu'Appropo ne lise jamais un fichier partiel.
     *
     * @throws \RuntimeException
     */
    public function upload(string $localPath, string $remoteDir, string $remoteName): void
    {
        $sftp = $this->connect();
        $base = 'ssh2.sftp://' . (int) $sftp . '/' . trim($remoteDir, '/') . '/';
        $tmpName = $remoteName . '.tmp';

        $in = fopen($localPath, 'r');
        $out = @fopen($base . $tmpName, 'w');
        if ($in === false || $out === false) {
            throw new \RuntimeException(sprintf('Ouverture impossible (local: %s, distant: %s%s)', $localPath, $remoteDir, $tmpName));
        }

        $bytes = stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        if ($bytes === false || $bytes !== filesize($localPath)) {
            @ssh2_sftp_unlink($sftp, '/' . trim($remoteDir, '/') . '/' . $tmpName);
            throw new \RuntimeException('Envoi SFTP incomplet.');
        }

        $remoteFinal = '/' . trim($remoteDir, '/') . '/' . $remoteName;
        // ponytail: unlink puis rename (rename écrase mal selon les serveurs SFTP), fenêtre de quelques ms sans fichier.
        @ssh2_sftp_unlink($sftp, $remoteFinal);
        if (!ssh2_sftp_rename($sftp, '/' . trim($remoteDir, '/') . '/' . $tmpName, $remoteFinal)) {
            throw new \RuntimeException('Renommage SFTP impossible vers ' . $remoteFinal);
        }

        $this->logger->info('[Appropo] Fichier déposé', ['remote' => $remoteFinal, 'bytes' => $bytes]);
    }

    /**
     * @return string[] noms présents dans le dossier distant
     */
    public function list(string $remoteDir): array
    {
        $sftp = $this->connect();
        $handle = @opendir('ssh2.sftp://' . (int) $sftp . '/' . trim($remoteDir, '/'));
        if ($handle === false) {
            throw new \RuntimeException('Dossier distant illisible : ' . $remoteDir);
        }

        $files = [];
        while (false !== ($file = readdir($handle))) {
            $files[] = $file;
        }
        closedir($handle);

        return $files;
    }

    /** @return resource */
    private function connect()
    {
        $connection = @ssh2_connect($this->host, $this->port);
        if (!$connection) {
            throw new \RuntimeException(sprintf('Connexion SSH impossible vers %s:%d', $this->host, $this->port));
        }

        if (!@ssh2_auth_password($connection, $this->username, $this->password)) {
            throw new \RuntimeException('Authentification SFTP refusée pour ' . $this->username);
        }

        $sftp = ssh2_sftp($connection);
        if (!$sftp) {
            throw new \RuntimeException('Sous-système SFTP indisponible.');
        }

        return $sftp;
    }
}
