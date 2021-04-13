<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210413175734 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE size_item (size_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_74BEE3BA498DA827 (size_id), INDEX IDX_74BEE3BA126F525E (item_id), PRIMARY KEY(size_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE size_item ADD CONSTRAINT FK_74BEE3BA498DA827 FOREIGN KEY (size_id) REFERENCES size (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE size_item ADD CONSTRAINT FK_74BEE3BA126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE size_item');
    }
}
