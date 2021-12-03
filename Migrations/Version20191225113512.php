<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191225113512 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fiat_withdrawal (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', account_id INT NOT NULL, amount NUMERIC(19, 8) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_454211EA76ED395 (user_id), INDEX IDX_454211E9B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiat_withdrawal ADD CONSTRAINT FK_454211EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fiat_withdrawal ADD CONSTRAINT FK_454211E9B6B5FBA FOREIGN KEY (account_id) REFERENCES user_withdraw_account (id)');
        $this->addSql('ALTER TABLE crypto_withdrawal DROP FOREIGN KEY FK_6D2D3B4538248176');
        $this->addSql('DROP INDEX IDX_6D2D3B4538248176 ON crypto_withdrawal');
        $this->addSql('ALTER TABLE crypto_withdrawal DROP currency_id');
        $this->addSql('ALTER TABLE crypto_withdrawal RENAME INDEX idx_6d2d3b45a76ed395 TO IDX_6E7D8A71A76ED395');
        $this->addSql('ALTER TABLE crypto_withdrawal RENAME INDEX idx_6d2d3b455e7836ea TO IDX_6E7D8A715E7836EA');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE fiat_withdrawal');
        $this->addSql('ALTER TABLE crypto_withdrawal ADD currency_id INT NOT NULL');
        $this->addSql('ALTER TABLE crypto_withdrawal ADD CONSTRAINT FK_6D2D3B4538248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_6D2D3B4538248176 ON crypto_withdrawal (currency_id)');
        $this->addSql('ALTER TABLE crypto_withdrawal RENAME INDEX idx_6e7d8a71a76ed395 TO IDX_6D2D3B45A76ED395');
        $this->addSql('ALTER TABLE crypto_withdrawal RENAME INDEX idx_6e7d8a715e7836ea TO IDX_6D2D3B455E7836EA');
    }
}
