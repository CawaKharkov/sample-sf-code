<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104190847 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DELETE FROM exchange_order WHERE price IS NULL');
        $this->addSql('ALTER TABLE exchange_order ADD amount_buy NUMERIC(19, 8) DEFAULT NULL, ADD amount_pay NUMERIC(19, 8) DEFAULT NULL, ADD amount_sell NUMERIC(19, 8) DEFAULT NULL, ADD amount_get NUMERIC(19, 8) DEFAULT NULL, DROP amount_filled, DROP price_filled, CHANGE price price NUMERIC(19, 8) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exchange_order ADD amount_filled NUMERIC(19, 8) DEFAULT NULL, ADD price_filled NUMERIC(19, 8) DEFAULT NULL, DROP amount_buy, DROP amount_pay, DROP amount_sell, DROP amount_get, CHANGE price price NUMERIC(19, 8) DEFAULT NULL');
    }
}
