<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200604083611 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_filling_operation DROP FOREIGN KEY FK_CAF9A9A78D9F6D38');
        $this->addSql('ALTER TABLE order_filling_operation ADD CONSTRAINT FK_CAF9A9A78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_filling_operation DROP FOREIGN KEY FK_CAF9A9A78D9F6D38');
        $this->addSql('ALTER TABLE order_filling_operation ADD CONSTRAINT FK_CAF9A9A78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
    }
}
