<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191119101204 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD identity_citizenship VARCHAR(100) DEFAULT NULL, ADD identity_type SMALLINT DEFAULT NULL, ADD identity_number VARCHAR(50) DEFAULT NULL, ADD identity_issue_date DATE DEFAULT NULL, ADD identity_expiry_date DATE DEFAULT NULL, ADD identity_issuer VARCHAR(100) DEFAULT NULL, ADD additional_politic_person TINYINT(1) DEFAULT NULL, ADD additional_politic_family TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP identity_citizenship, DROP identity_type, DROP identity_number, DROP identity_issue_date, DROP identity_expiry_date, DROP identity_issuer, DROP additional_politic_person, DROP additional_politic_family');
    }
}
