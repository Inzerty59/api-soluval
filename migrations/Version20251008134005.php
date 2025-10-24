<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251008134005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne category_id à la table part';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE part ADD category_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE part DROP category_id');
    }
}