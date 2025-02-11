<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209231123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE archived_item_id_seq CASCADE');
        $this->addSql('ALTER TABLE archived_item DROP CONSTRAINT fk_archived_item_user_fk');
        $this->addSql('DROP TABLE archived_item');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE archived_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE archived_item (id INT NOT NULL, user_id INT NOT NULL, item_type VARCHAR(255) NOT NULL, item_id INT NOT NULL, archived_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_4b6ecd71a76ed395 ON archived_item (user_id)');
        $this->addSql('COMMENT ON COLUMN archived_item.archived_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE archived_item ADD CONSTRAINT fk_archived_item_user_fk FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
