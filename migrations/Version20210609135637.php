<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609135637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_list_item (id INT AUTO_INCREMENT NOT NULL, order_list_id INT NOT NULL, delivery_address VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, discount DOUBLE PRECISION NOT NULL, price_without_discount DOUBLE PRECISION NOT NULL, total_price DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_E652810FCBD4BFC0 (order_list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_list_item ADD CONSTRAINT FK_E652810FCBD4BFC0 FOREIGN KEY (order_list_id) REFERENCES order_list (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE order_list_item');
    }
}
