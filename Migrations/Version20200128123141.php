<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200128123141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rate ADD currency_from_id INT DEFAULT NULL, ADD currency_to_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rate ADD CONSTRAINT FK_DFEC3F39A56723E4 FOREIGN KEY (currency_from_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE rate ADD CONSTRAINT FK_DFEC3F3967D74803 FOREIGN KEY (currency_to_id) REFERENCES currency (id)');
        $this->addSql('CREATE INDEX IDX_DFEC3F39A56723E4 ON rate (currency_from_id)');
        $this->addSql('CREATE INDEX IDX_DFEC3F3967D74803 ON rate (currency_to_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE rate DROP FOREIGN KEY FK_DFEC3F39A56723E4');
        $this->addSql('ALTER TABLE rate DROP FOREIGN KEY FK_DFEC3F3967D74803');
        $this->addSql('DROP INDEX IDX_DFEC3F39A56723E4 ON rate');
        $this->addSql('DROP INDEX IDX_DFEC3F3967D74803 ON rate');
        $this->addSql('ALTER TABLE rate DROP currency_from_id, DROP currency_to_id');
    }
}
