<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207145041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE billing_adress (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, shipping_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, street_additionnal VARCHAR(255) DEFAULT NULL, post_code VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_1936E4EEA76ED395 (user_id), INDEX IDX_1936E4EE4887F3F8 (shipping_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE delivery_adress (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, shipping_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, street_additionnal VARCHAR(255) DEFAULT NULL, post_code VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country_id VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_42FD3E30A76ED395 (user_id), INDEX IDX_42FD3E304887F3F8 (shipping_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mango_pay (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, wallet_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_access_token (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT \'(DC2Type:oauth2_scope)\', revoked TINYINT(1) NOT NULL, INDEX IDX_454D9673C7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_authorization_code (identifier CHAR(80) NOT NULL, client VARCHAR(32) NOT NULL, expiry DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', user_identifier VARCHAR(128) DEFAULT NULL, scopes TEXT DEFAULT NULL COMMENT \'(DC2Type:oauth2_scope)\', revoked TINYINT(1) NOT NULL, INDEX IDX_509FEF5FC7440455 (client), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_client (identifier VARCHAR(32) NOT NULL, name VARCHAR(128) NOT NULL, secret VARCHAR(128) DEFAULT NULL, redirect_uris TEXT DEFAULT NULL COMMENT \'(DC2Type:oauth2_redirect_uri)\', grants TEXT DEFAULT NULL COMMENT \'(DC2Type:oauth2_grant)\', scopes TEXT DEFAULT NULL COMMENT \'(DC2Type:oauth2_scope)\', active TINYINT(1) NOT NULL, allow_plain_text_pkce TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth2_refresh_token (identifier CHAR(80) NOT NULL, access_token CHAR(80) DEFAULT NULL, expiry DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', revoked TINYINT(1) NOT NULL, INDEX IDX_4DD90732B6A2DD68 (access_token), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, billing_adress_id INT DEFAULT NULL, delivery_adress_id INT DEFAULT NULL, mango_pay_id INT DEFAULT NULL, to_send TINYINT(1) NOT NULL, is_free_shipping TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', order_number VARCHAR(255) NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F529939830959BF2 (billing_adress_id), INDEX IDX_F5299398C0E3B53E (delivery_adress_id), INDEX IDX_F5299398574FB19E (mango_pay_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part (id INT AUTO_INCREMENT NOT NULL, external_id INT NOT NULL, manufacturer_reference VARCHAR(40) DEFAULT NULL, adaptable_reference VARCHAR(40) DEFAULT NULL, category_name VARCHAR(80) DEFAULT NULL, description VARCHAR(1000) DEFAULT NULL, part_condition INT DEFAULT NULL, warranty INT DEFAULT NULL, brand_name VARCHAR(80) DEFAULT NULL, range_name VARCHAR(80) DEFAULT NULL, model_name VARCHAR(80) DEFAULT NULL, finish_name VARCHAR(255) DEFAULT NULL, commercial_designation VARCHAR(255) DEFAULT NULL, vehicle_year INT DEFAULT NULL, mileage INT DEFAULT NULL, color_name LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', displacement INT DEFAULT NULL, power VARCHAR(80) DEFAULT NULL, energy_name VARCHAR(80) DEFAULT NULL, gearbox_type LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', engine_code VARCHAR(80) DEFAULT NULL, gearbox_code VARCHAR(80) DEFAULT NULL, door_number INT DEFAULT NULL, vignette VARCHAR(255) DEFAULT NULL, photos LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', price LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', casse_id INT NOT NULL, shipping_id INT NOT NULL, weight VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE part_order (part_id INT NOT NULL, order_id INT NOT NULL, INDEX IDX_665AC47B4CE34BEC (part_id), INDEX IDX_665AC47B8D9F6D38 (order_id), PRIMARY KEY(part_id, order_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shippings (id INT AUTO_INCREMENT NOT NULL, shipping_id INT NOT NULL, title VARCHAR(255) NOT NULL, coefficient INT NOT NULL, cost VARCHAR(255) NOT NULL, cost_excluding_taxes VARCHAR(255) NOT NULL, is_delivery_available TINYINT(1) NOT NULL, vatrate VARCHAR(255) NOT NULL, delay_min VARCHAR(255) NOT NULL, delay_max VARCHAR(255) NOT NULL, country_id INT NOT NULL, isocode VARCHAR(255) NOT NULL, discount_part2 VARCHAR(255) NOT NULL, discount_part3 VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, company_name VARCHAR(255) DEFAULT NULL, siret_number VARCHAR(14) DEFAULT NULL, vat_number VARCHAR(20) DEFAULT NULL, account_type VARCHAR(255) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE billing_adress ADD CONSTRAINT FK_1936E4EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE billing_adress ADD CONSTRAINT FK_1936E4EE4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shippings (id)');
        $this->addSql('ALTER TABLE delivery_adress ADD CONSTRAINT FK_42FD3E30A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE delivery_adress ADD CONSTRAINT FK_42FD3E304887F3F8 FOREIGN KEY (shipping_id) REFERENCES shippings (id)');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673C7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth2_authorization_code ADD CONSTRAINT FK_509FEF5FC7440455 FOREIGN KEY (client) REFERENCES oauth2_client (identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732B6A2DD68 FOREIGN KEY (access_token) REFERENCES oauth2_access_token (identifier) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939830959BF2 FOREIGN KEY (billing_adress_id) REFERENCES billing_adress (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398C0E3B53E FOREIGN KEY (delivery_adress_id) REFERENCES delivery_adress (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398574FB19E FOREIGN KEY (mango_pay_id) REFERENCES mango_pay (id)');
        $this->addSql('ALTER TABLE part_order ADD CONSTRAINT FK_665AC47B4CE34BEC FOREIGN KEY (part_id) REFERENCES part (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE part_order ADD CONSTRAINT FK_665AC47B8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billing_adress DROP FOREIGN KEY FK_1936E4EEA76ED395');
        $this->addSql('ALTER TABLE billing_adress DROP FOREIGN KEY FK_1936E4EE4887F3F8');
        $this->addSql('ALTER TABLE delivery_adress DROP FOREIGN KEY FK_42FD3E30A76ED395');
        $this->addSql('ALTER TABLE delivery_adress DROP FOREIGN KEY FK_42FD3E304887F3F8');
        $this->addSql('ALTER TABLE oauth2_access_token DROP FOREIGN KEY FK_454D9673C7440455');
        $this->addSql('ALTER TABLE oauth2_authorization_code DROP FOREIGN KEY FK_509FEF5FC7440455');
        $this->addSql('ALTER TABLE oauth2_refresh_token DROP FOREIGN KEY FK_4DD90732B6A2DD68');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939830959BF2');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398C0E3B53E');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398574FB19E');
        $this->addSql('ALTER TABLE part_order DROP FOREIGN KEY FK_665AC47B4CE34BEC');
        $this->addSql('ALTER TABLE part_order DROP FOREIGN KEY FK_665AC47B8D9F6D38');
        $this->addSql('DROP TABLE billing_adress');
        $this->addSql('DROP TABLE delivery_adress');
        $this->addSql('DROP TABLE mango_pay');
        $this->addSql('DROP TABLE oauth2_access_token');
        $this->addSql('DROP TABLE oauth2_authorization_code');
        $this->addSql('DROP TABLE oauth2_client');
        $this->addSql('DROP TABLE oauth2_refresh_token');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE part_order');
        $this->addSql('DROP TABLE shippings');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
