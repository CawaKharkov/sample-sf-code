<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109115159 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_deposit_method (id INT AUTO_INCREMENT NOT NULL, currency_id INT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, account_name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, iban VARCHAR(255) NOT NULL, bank_name VARCHAR(255) NOT NULL, bic VARCHAR(20) NOT NULL, bank_address VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) NOT NULL, INDEX IDX_2457B87838248176 (currency_id), INDEX IDX_2457B878A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_deposit_method ADD CONSTRAINT FK_2457B87838248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE user_deposit_method ADD CONSTRAINT FK_2457B878A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_deposit_method');
    }
}
