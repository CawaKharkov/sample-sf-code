<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191223092854 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_deposit_cryptoaddress RENAME INDEX idx_b7b71637a76ed395 TO IDX_8F470F45A76ED395');
        $this->addSql('ALTER TABLE user_deposit_cryptoaddress RENAME INDEX idx_b7b7163738248176 TO IDX_8F470F4538248176');

        $this->addSql('CREATE TABLE user_withdraw_cryptoaddress (id INT AUTO_INCREMENT NOT NULL, user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', currency_id INT NOT NULL, address VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_B060DE87A76ED395 (user_id), INDEX IDX_B060DE8738248176 (currency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_withdraw_cryptoaddress ADD CONSTRAINT FK_B060DE87A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_withdraw_cryptoaddress ADD CONSTRAINT FK_B060DE8738248176 FOREIGN KEY (currency_id) REFERENCES currency (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_deposit_cryptoaddress RENAME INDEX idx_8f470f4538248176 TO IDX_B7B7163738248176');
        $this->addSql('ALTER TABLE user_deposit_cryptoaddress RENAME INDEX idx_8f470f45a76ed395 TO IDX_B7B71637A76ED395');

        $this->addSql('DROP TABLE user_withdraw_cryptoaddress');
    }
}
