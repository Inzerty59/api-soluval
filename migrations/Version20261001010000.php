<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261001010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Index sur part.external_id (manquant jusque-là, scanné en entier à chaque lecture/écriture)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PART_EXTERNAL_ID ON part (external_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_PART_EXTERNAL_ID ON part');
    }
}
