<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261106000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table appropo_unstock : pièces vendues à signaler à Appropo (fichier hour/)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE appropo_unstock (id INT AUTO_INCREMENT NOT NULL, external_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_appropo_unstock_sent_at (sent_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE appropo_unstock');
    }
}
