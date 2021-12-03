<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200312074112 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX from_to_idx ON rate');
        $this->addSql('ALTER TABLE multinode_request_update_item_results ADD receive_fee NUMERIC(19, 8) DEFAULT NULL, ADD receive_fee_currency VARCHAR(10) DEFAULT NULL, ADD state INT DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, ADD confirmed_at DATETIME DEFAULT NULL, ADD crypto_broker_id VARCHAR(255) DEFAULT NULL, ADD coinbase_txid VARCHAR(255) DEFAULT NULL, ADD __v SMALLINT DEFAULT NULL, ADD _id VARCHAR(255) DEFAULT NULL, CHANGE fee_big fee NUMERIC(19, 8) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE multinode_request_update_item_results ADD fee_big NUMERIC(19, 8) DEFAULT NULL, DROP fee, DROP receive_fee, DROP receive_fee_currency, DROP state, DROP created_at, DROP confirmed_at, DROP crypto_broker_id, DROP coinbase_txid, DROP __v, DROP _id');
        $this->addSql('CREATE INDEX from_to_idx ON rate (currency_from_id, currency_to_id)');
    }
}
