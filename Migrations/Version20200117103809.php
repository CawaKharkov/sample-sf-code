<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200117103809 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE crypto_deposit (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', currency_id INT NOT NULL, cryptoaddress_id INT NOT NULL, amount NUMERIC(19, 8) NOT NULL, fee NUMERIC(19, 8) DEFAULT NULL, tx_db_id VARCHAR(255) NOT NULL, tx_id VARCHAR(255) NOT NULL, confirmations INT NOT NULL, created_at DATETIME NOT NULL, chain_confirmed_at DATETIME DEFAULT NULL, INDEX IDX_44A73BD6A76ED395 (user_id), INDEX IDX_44A73BD638248176 (currency_id), INDEX IDX_44A73BD65E7836EA (cryptoaddress_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crypto_deposit ADD CONSTRAINT FK_44A73BD6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE crypto_deposit ADD CONSTRAINT FK_44A73BD638248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE crypto_deposit ADD CONSTRAINT FK_44A73BD65E7836EA FOREIGN KEY (cryptoaddress_id) REFERENCES user_deposit_cryptoaddress (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE crypto_deposit');
    }
}
