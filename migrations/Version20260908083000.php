<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260908083000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la table global_pre_order (idempotence des commandes Global PRE)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE global_pre_order (id INT AUTO_INCREMENT NOT NULL, order_id VARCHAR(100) NOT NULL, opisto_order_id INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_ORDER_ID (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE global_pre_order');
    }
}
