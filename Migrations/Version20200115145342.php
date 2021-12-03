<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115145342 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE multinode_request_update_item ADD multinode_request_update_id INT NOT NULL');
        $this->addSql('ALTER TABLE multinode_request_update_item ADD CONSTRAINT FK_2AC323CCCCF5A445 FOREIGN KEY (multinode_request_update_id) REFERENCES multinode_request_update (id)');
        $this->addSql('CREATE INDEX IDX_2AC323CCCCF5A445 ON multinode_request_update_item (multinode_request_update_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE multinode_request_update_item DROP FOREIGN KEY FK_2AC323CCCCF5A445');
        $this->addSql('DROP INDEX IDX_2AC323CCCCF5A445 ON multinode_request_update_item');
        $this->addSql('ALTER TABLE multinode_request_update_item DROP multinode_request_update_id');
    }
}
