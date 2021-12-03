<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191119060221 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD phone VARCHAR(20) DEFAULT NULL, ADD gender SMALLINT DEFAULT NULL, ADD birth_date DATE DEFAULT NULL, ADD birth_place VARCHAR(255) DEFAULT NULL, ADD card_name VARCHAR(50) DEFAULT NULL, ADD residence_country VARCHAR(50) DEFAULT NULL, ADD residence_street VARCHAR(100) DEFAULT NULL, ADD residence_house VARCHAR(10) DEFAULT NULL, ADD residence_apartment VARCHAR(10) DEFAULT NULL, ADD residence_city VARCHAR(100) DEFAULT NULL, ADD residence_province VARCHAR(50) DEFAULT NULL, ADD residence_postal_code VARCHAR(20) DEFAULT NULL, ADD delivery_country VARCHAR(100) DEFAULT NULL, ADD delivery_city VARCHAR(100) DEFAULT NULL, ADD delivery_index VARCHAR(20) DEFAULT NULL, ADD delivery_address VARCHAR(255) DEFAULT NULL, ADD delivery_recipient VARCHAR(100) DEFAULT NULL, ADD deivery_option SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP phone, DROP gender, DROP birth_date, DROP birth_place, DROP card_name, DROP residence_country, DROP residence_street, DROP residence_house, DROP residence_apartment, DROP residence_city, DROP residence_province, DROP residence_postal_code, DROP delivery_country, DROP delivery_city, DROP delivery_index, DROP delivery_address, DROP delivery_recipient, DROP deivery_option');
    }
}
