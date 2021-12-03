<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201103091130 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exchange_order DROP FOREIGN KEY FK_EB1EDFD03CA6CA4');
        $this->addSql('DROP INDEX IDX_EB1EDFD03CA6CA4 ON exchange_order');
        $this->addSql('ALTER TABLE exchange_order CHANGE exchange_direction_id direction_id INT NOT NULL');
        $this->addSql('ALTER TABLE exchange_order ADD CONSTRAINT FK_EB1EDFD0AF73D997 FOREIGN KEY (direction_id) REFERENCES exchange_direction (id)');
        $this->addSql('CREATE INDEX IDX_EB1EDFD0AF73D997 ON exchange_order (direction_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exchange_order DROP FOREIGN KEY FK_EB1EDFD0AF73D997');
        $this->addSql('DROP INDEX IDX_EB1EDFD0AF73D997 ON exchange_order');
        $this->addSql('ALTER TABLE exchange_order CHANGE direction_id exchange_direction_id INT NOT NULL');
        $this->addSql('ALTER TABLE exchange_order ADD CONSTRAINT FK_EB1EDFD03CA6CA4 FOREIGN KEY (exchange_direction_id) REFERENCES exchange_direction (id)');
        $this->addSql('CREATE INDEX IDX_EB1EDFD03CA6CA4 ON exchange_order (exchange_direction_id)');
    }
}
