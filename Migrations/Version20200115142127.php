<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200115142127 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE multinode_request_update (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multinode_request_update_item (id INT AUTO_INCREMENT NOT NULL, chain VARCHAR(20) NOT NULL, has_new_block TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE multinode_request_update_item_results (id INT AUTO_INCREMENT NOT NULL, multinode_request_update_item_id INT NOT NULL, chain VARCHAR(20) NOT NULL, update_state VARCHAR(50) NOT NULL, user VARCHAR(255) NOT NULL, category VARCHAR(50) NOT NULL, address VARCHAR(255) NOT NULL, address_to VARCHAR(255) NOT NULL, address_from VARCHAR(255) NOT NULL, txid VARCHAR(255) NOT NULL, fee NUMERIC(19, 8) NOT NULL, amount NUMERIC(19, 8) NOT NULL, confirmations INT NOT NULL, tx_db_id VARCHAR(255) NOT NULL, is_coinbase_for VARCHAR(255) NOT NULL, domain_name VARCHAR(255) NOT NULL, INDEX IDX_24BA2FAF42304E55 (multinode_request_update_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE multinode_request_update_item_results ADD CONSTRAINT FK_24BA2FAF42304E55 FOREIGN KEY (multinode_request_update_item_id) REFERENCES multinode_request_update_item (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE multinode_request_update_item_results DROP FOREIGN KEY FK_24BA2FAF42304E55');
        $this->addSql('DROP TABLE multinode_request_update');
        $this->addSql('DROP TABLE multinode_request_update_item');
        $this->addSql('DROP TABLE multinode_request_update_item_results');
    }
}
