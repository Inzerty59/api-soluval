<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261001000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute type_mine et code_couleur sur part (champs véhicule Opisto non captés jusque-là)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE part ADD type_mine VARCHAR(100) DEFAULT NULL, ADD code_couleur VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE part DROP type_mine, DROP code_couleur');
    }
}
