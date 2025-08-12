<?php

namespace App\Service\FranceCasse;

class SftpCheckerService
{
    private string $host;
    private string $username;
    private string $password;
    private string $remotePath;

    public function __construct(string $host, string $username, string $password, string $remotePath = 'upload')
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->remotePath = $remotePath;
    }

    /**
     * Retourne la liste des fichiers JSON présents sur le serveur SFTP.
     *
     * @return array
     * @throws \Exception
     */
    public function getJsonFiles(): array
    {
        $connection = ssh2_connect($this->host, 22);
        if (!$connection) {
            throw new \Exception('Connexion SSH échouée');
        }

        if (!ssh2_auth_password($connection, $this->username, $this->password)) {
            throw new \Exception('Échec de l\'authentification');
        }

        $sftp = ssh2_sftp($connection);
        $dirHandle = opendir("ssh2.sftp://$sftp/{$this->remotePath}");

        $jsonFiles = [];
        while (false !== ($file = readdir($dirHandle))) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $jsonFiles[] = $file;
            }
        }

        closedir($dirHandle);

        return $jsonFiles;
    }
    public function getFileContent(string $fileName): string
{
    $connection = ssh2_connect($this->host, 22);
    if (!$connection) throw new \Exception('Connexion SSH échouée');
    if (!ssh2_auth_password($connection, $this->username, $this->password)) {
        throw new \Exception('Échec de l\'authentification');
    }
    $sftp = ssh2_sftp($connection);

    $path = "ssh2.sftp://$sftp/{$this->remotePath}/$fileName";
    $stream = @fopen($path, 'r');
    if (!$stream) throw new \Exception("Impossible d'ouvrir le fichier : $fileName");

    $content = stream_get_contents($stream);
    fclose($stream);
    return $content !== false ? $content : '';
}

public function moveFile(string $fileName, string $targetSubDir): void
{
    $connection = ssh2_connect($this->host, 22);
    if (!$connection) throw new \Exception('Connexion SSH échouée');
    if (!ssh2_auth_password($connection, $this->username, $this->password)) {
        throw new \Exception('Échec de l\'authentification');
    }
    $sftp = ssh2_sftp($connection);

    $targetDir = trim($targetSubDir, '/');
    $source = "/{$this->remotePath}/$fileName";
    $destDir = "/{$this->remotePath}/$targetDir";
    $dest = "$destDir/$fileName";

    // crée le dossier cible si besoin
    if (!@opendir("ssh2.sftp://$sftp$destDir")) {
        @ssh2_sftp_mkdir($sftp, $destDir, 0775, true);
    }
    if (!@ssh2_sftp_rename($sftp, $source, $dest)) {
        throw new \Exception("Impossible de déplacer $fileName vers $targetSubDir");
    }
}

}
