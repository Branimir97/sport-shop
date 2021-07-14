<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210610100721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item ADD order_list_item_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09CAE01B39 FOREIGN KEY (order_list_item_id) REFERENCES order_list_item (id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09CAE01B39 ON order_item (order_list_item_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09CAE01B39');
        $this->addSql('DROP INDEX IDX_52EA1F09CAE01B39 ON order_item');
        $this->addSql('ALTER TABLE order_item DROP order_list_item_id');
    }
}
