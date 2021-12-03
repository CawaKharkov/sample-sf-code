<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191120090937 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP scan_name, DROP scan_address, DROP scan_passport, DROP scan_name_verified, DROP scan_address_verified, DROP scan_passport_verified');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD scan_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD scan_address VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD scan_passport VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD scan_name_verified TINYINT(1) DEFAULT NULL, ADD scan_address_verified TINYINT(1) DEFAULT NULL, ADD scan_passport_verified TINYINT(1) DEFAULT NULL');
    }
}
