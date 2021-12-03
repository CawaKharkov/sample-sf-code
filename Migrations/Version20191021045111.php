<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021045111 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, middle_name VARCHAR(100) DEFAULT NULL, timezone SMALLINT DEFAULT NULL, two_factor_email TINYINT(1) DEFAULT NULL, two_factor_google TINYINT(1) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, address_city VARCHAR(50) DEFAULT NULL, address_zip VARCHAR(20) DEFAULT NULL, id_number VARCHAR(20) DEFAULT NULL, id_country VARCHAR(50) DEFAULT NULL, id_issued_by VARCHAR(100) DEFAULT NULL, scan_name VARCHAR(255) DEFAULT NULL, scan_address VARCHAR(255) DEFAULT NULL, scan_passport VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, symbol VARCHAR(10) NOT NULL, `precision` SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE direction (id INT AUTO_INCREMENT NOT NULL, currency_from_id INT NOT NULL, currency_to_id INT NOT NULL, minimum_amount BIGINT NOT NULL, maximum_amount BIGINT NOT NULL, code VARCHAR(10) NOT NULL, `precision` SMALLINT NOT NULL, INDEX IDX_3E4AD1B3A56723E4 (currency_from_id), INDEX IDX_3E4AD1B367D74803 (currency_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', direction_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION DEFAULT NULL, type SMALLINT NOT NULL, status SMALLINT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F5299398AF73D997 (direction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE secret_question (id INT AUTO_INCREMENT NOT NULL, question VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_account (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', currency_id INT NOT NULL, balance BIGINT NOT NULL, INDEX IDX_253B48AEA76ED395 (user_id), INDEX IDX_253B48AE38248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_payment_method (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', asset_id INT NOT NULL, title VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, INDEX IDX_10E47EAFA76ED395 (user_id), INDEX IDX_10E47EAF5DA1941 (asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_secret_question_answer (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', question_id INT NOT NULL, answer VARCHAR(255) NOT NULL, INDEX IDX_F7BAC310A76ED395 (user_id), INDEX IDX_F7BAC3101E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_white_ip (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', ip VARCHAR(20) NOT NULL, INDEX IDX_37E87691A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE direction ADD CONSTRAINT FK_3E4AD1B3A56723E4 FOREIGN KEY (currency_from_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE direction ADD CONSTRAINT FK_3E4AD1B367D74803 FOREIGN KEY (currency_to_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398AF73D997 FOREIGN KEY (direction_id) REFERENCES direction (id)');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_account ADD CONSTRAINT FK_253B48AE38248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE user_payment_method ADD CONSTRAINT FK_10E47EAFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_payment_method ADD CONSTRAINT FK_10E47EAF5DA1941 FOREIGN KEY (asset_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE user_secret_question_answer ADD CONSTRAINT FK_F7BAC310A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_secret_question_answer ADD CONSTRAINT FK_F7BAC3101E27F6BF FOREIGN KEY (question_id) REFERENCES secret_question (id)');
        $this->addSql('ALTER TABLE user_white_ip ADD CONSTRAINT FK_37E87691A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE user_account DROP FOREIGN KEY FK_253B48AEA76ED395');
        $this->addSql('ALTER TABLE user_payment_method DROP FOREIGN KEY FK_10E47EAFA76ED395');
        $this->addSql('ALTER TABLE user_secret_question_answer DROP FOREIGN KEY FK_F7BAC310A76ED395');
        $this->addSql('ALTER TABLE user_white_ip DROP FOREIGN KEY FK_37E87691A76ED395');
        $this->addSql('ALTER TABLE direction DROP FOREIGN KEY FK_3E4AD1B3A56723E4');
        $this->addSql('ALTER TABLE direction DROP FOREIGN KEY FK_3E4AD1B367D74803');
        $this->addSql('ALTER TABLE user_account DROP FOREIGN KEY FK_253B48AE38248176');
        $this->addSql('ALTER TABLE user_payment_method DROP FOREIGN KEY FK_10E47EAF5DA1941');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398AF73D997');
        $this->addSql('ALTER TABLE user_secret_question_answer DROP FOREIGN KEY FK_F7BAC3101E27F6BF');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE direction');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE secret_question');
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP TABLE user_payment_method');
        $this->addSql('DROP TABLE user_secret_question_answer');
        $this->addSql('DROP TABLE user_white_ip');
    }
}
