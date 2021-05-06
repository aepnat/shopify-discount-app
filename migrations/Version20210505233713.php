<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505233713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discount_codes (id INT AUTO_INCREMENT NOT NULL, shop_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(40) NOT NULL, value VARCHAR(20) NOT NULL, once_per_customer TINYINT(1) DEFAULT NULL, price_rule_id BIGINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_CE8719DA4D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shops (id BIGINT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE discount_codes ADD CONSTRAINT FK_CE8719DA4D16C4DD FOREIGN KEY (shop_id) REFERENCES shops (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE discount_codes DROP FOREIGN KEY FK_CE8719DA4D16C4DD');
        $this->addSql('DROP TABLE discount_codes');
        $this->addSql('DROP TABLE shops');
    }
}
