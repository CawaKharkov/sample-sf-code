<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102100837 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE exchange_direction (id INT AUTO_INCREMENT NOT NULL, currency_base_id INT NOT NULL, currency_quote_id INT NOT NULL, minimum_amount BIGINT NOT NULL, maximum_amount BIGINT NOT NULL, code VARCHAR(10) NOT NULL, `precision` SMALLINT NOT NULL, INDEX IDX_73624892B4CE25AE (currency_base_id), INDEX IDX_73624892EBE8076B (currency_quote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE exchange_order (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', exchange_direction_id INT NOT NULL, amount NUMERIC(19, 8) NOT NULL, amount_filled NUMERIC(19, 8) DEFAULT NULL, price NUMERIC(19, 8) DEFAULT NULL, price_filled NUMERIC(19, 8) DEFAULT NULL, type SMALLINT NOT NULL, status SMALLINT NOT NULL, core_state SMALLINT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, closed_at DATETIME DEFAULT NULL, INDEX IDX_EB1EDFD0A76ED395 (user_id), INDEX IDX_EB1EDFD03CA6CA4 (exchange_direction_id), INDEX IDX_USER_STATUS (user_id, status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE exchange_direction ADD CONSTRAINT FK_73624892B4CE25AE FOREIGN KEY (currency_base_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE exchange_direction ADD CONSTRAINT FK_73624892EBE8076B FOREIGN KEY (currency_quote_id) REFERENCES currency (id)');
        $this->addSql('ALTER TABLE exchange_order ADD CONSTRAINT FK_EB1EDFD0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE exchange_order ADD CONSTRAINT FK_EB1EDFD03CA6CA4 FOREIGN KEY (exchange_direction_id) REFERENCES exchange_direction (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE exchange_order DROP FOREIGN KEY FK_EB1EDFD03CA6CA4');
        $this->addSql('DROP TABLE exchange_direction');
        $this->addSql('DROP TABLE exchange_order');
    }
}
