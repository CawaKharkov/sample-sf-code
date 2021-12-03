<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200110133854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_deposit_method DROP name, DROP account_name, DROP address, DROP iban, DROP bank_name, DROP bic, DROP bank_address');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_deposit_method ADD name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD account_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD address VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD iban VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD bank_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD bic VARCHAR(20) NOT NULL COLLATE utf8mb4_unicode_ci, ADD bank_address VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
