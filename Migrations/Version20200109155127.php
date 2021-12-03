<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109155127 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deposit_transaction ADD user_deposit_method_id INT NOT NULL');
        $this->addSql('ALTER TABLE deposit_transaction ADD CONSTRAINT FK_907A7426E5B01C1B FOREIGN KEY (user_deposit_method_id) REFERENCES user_deposit_method (id)');
        $this->addSql('CREATE INDEX IDX_907A7426E5B01C1B ON deposit_transaction (user_deposit_method_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE deposit_transaction DROP FOREIGN KEY FK_907A7426E5B01C1B');
        $this->addSql('DROP INDEX IDX_907A7426E5B01C1B ON deposit_transaction');
        $this->addSql('ALTER TABLE deposit_transaction DROP user_deposit_method_id');
    }
}
