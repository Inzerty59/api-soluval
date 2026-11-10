<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Table appropo_order : idempotence des commandes Appropo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE appropo_order (id INT AUTO_INCREMENT NOT NULL, order_id VARCHAR(100) NOT NULL, opisto_order_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_6862FEB58D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE appropo_order');
    }
}
