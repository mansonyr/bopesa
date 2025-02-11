<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209210155 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archived_item ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE archived_item ALTER archived_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN archived_item.archived_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX idx_archived_item_user RENAME TO IDX_4B6ECD71A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE archived_item_id_seq');
        $this->addSql('SELECT setval(\'archived_item_id_seq\', (SELECT MAX(id) FROM archived_item))');
        $this->addSql('ALTER TABLE archived_item ALTER id SET DEFAULT nextval(\'archived_item_id_seq\')');
        $this->addSql('ALTER TABLE archived_item ALTER archived_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN archived_item.archived_at IS NULL');
        $this->addSql('ALTER INDEX idx_4b6ecd71a76ed395 RENAME TO idx_archived_item_user');
    }
}
