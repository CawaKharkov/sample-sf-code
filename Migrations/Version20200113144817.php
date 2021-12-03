<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200113144817 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE multinode_request_create (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multinode_request_create_item (id INT AUTO_INCREMENT NOT NULL, multinode_request_create_id INT NOT NULL, status VARCHAR(50) NOT NULL, tx_db_id VARCHAR(255) NOT NULL, tx_id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, INDEX IDX_7F0EE93AF77D9377 (multinode_request_create_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE multinode_request_create_item ADD CONSTRAINT FK_7F0EE93AF77D9377 FOREIGN KEY (multinode_request_create_id) REFERENCES multinode_request_create (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE multinode_request_create_item DROP FOREIGN KEY FK_7F0EE93AF77D9377');
        $this->addSql('DROP TABLE multinode_request_create');
        $this->addSql('DROP TABLE multinode_request_create_item');
    }
}
