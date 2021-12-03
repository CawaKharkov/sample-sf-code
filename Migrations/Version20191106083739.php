<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191106083739 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE order_filling (id INT AUTO_INCREMENT NOT NULL, taker_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', maker_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_at DATETIME NOT NULL, taker_price BIGINT DEFAULT NULL, taker_amount BIGINT NOT NULL, maker_price BIGINT DEFAULT NULL, maker_amount BIGINT NOT NULL, INDEX IDX_EE17C1F2B2E74C3 (taker_id), INDEX IDX_EE17C1F268DA5EC3 (maker_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_filling ADD CONSTRAINT FK_EE17C1F2B2E74C3 FOREIGN KEY (taker_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_filling ADD CONSTRAINT FK_EE17C1F268DA5EC3 FOREIGN KEY (maker_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE `order` CHANGE amount amount NUMERIC(19, 8) NOT NULL, CHANGE price price NUMERIC(19, 8) DEFAULT NULL');
        $this->addSql('ALTER TABLE rate CHANGE value value NUMERIC(19, 8) NOT NULL');
        $this->addSql('ALTER TABLE transaction CHANGE amount amount NUMERIC(19, 8) NOT NULL, CHANGE fee fee NUMERIC(19, 8) NOT NULL');
        $this->addSql('ALTER TABLE user_account CHANGE balance balance NUMERIC(19, 8) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE order_filling');
        $this->addSql('ALTER TABLE `order` CHANGE amount amount DOUBLE PRECISION NOT NULL, CHANGE price price DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE rate CHANGE value value DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE transaction CHANGE amount amount DOUBLE PRECISION NOT NULL, CHANGE fee fee DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE user_account CHANGE balance balance DOUBLE PRECISION NOT NULL');
    }
}
