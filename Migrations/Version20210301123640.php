<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210301123640 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE amlinspection (id INT AUTO_INCREMENT NOT NULL, fiat_withdrawal_id INT DEFAULT NULL, crypto_withdrawal_id INT DEFAULT NULL, crypto_deposit_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_2A5970B39060CB40 (fiat_withdrawal_id), INDEX IDX_2A5970B3DCAABA79 (crypto_withdrawal_id), INDEX IDX_2A5970B39B455585 (crypto_deposit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE amlinspection ADD CONSTRAINT FK_2A5970B39060CB40 FOREIGN KEY (fiat_withdrawal_id) REFERENCES fiat_withdrawal (id)');
        $this->addSql('ALTER TABLE amlinspection ADD CONSTRAINT FK_2A5970B3DCAABA79 FOREIGN KEY (crypto_withdrawal_id) REFERENCES crypto_withdrawal (id)');
        $this->addSql('ALTER TABLE amlinspection ADD CONSTRAINT FK_2A5970B39B455585 FOREIGN KEY (crypto_deposit_id) REFERENCES crypto_deposit (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE amlinspection');
    }
}
