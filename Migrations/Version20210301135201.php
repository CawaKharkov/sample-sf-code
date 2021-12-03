<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210301135201 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE amlinspection ADD deposit_transaction_id INT DEFAULT NULL, ADD last_inspected_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE amlinspection ADD CONSTRAINT FK_2A5970B3A102DB22 FOREIGN KEY (deposit_transaction_id) REFERENCES deposit_transaction (id)');
        $this->addSql('CREATE INDEX IDX_2A5970B3A102DB22 ON amlinspection (deposit_transaction_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE amlinspection DROP FOREIGN KEY FK_2A5970B3A102DB22');
        $this->addSql('DROP INDEX IDX_2A5970B3A102DB22 ON amlinspection');
        $this->addSql('ALTER TABLE amlinspection DROP deposit_transaction_id, DROP last_inspected_at');
    }
}
