<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209200553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isDefault field and create ArchivedItem table';
    }

    public function up(Schema $schema): void
    {
        // Add is_default column with default value
        $this->addSql('ALTER TABLE task ADD is_default BOOLEAN DEFAULT false');
        $this->addSql('ALTER TABLE sub_task ADD is_default BOOLEAN DEFAULT false');
        
        // Set default values for existing records
        $this->addSql('UPDATE task SET is_default = false');
        $this->addSql('UPDATE sub_task SET is_default = false');
        
        // Remove default and set not null constraint
        $this->addSql('ALTER TABLE task ALTER COLUMN is_default SET NOT NULL');
        $this->addSql('ALTER TABLE task ALTER COLUMN is_default DROP DEFAULT');
        $this->addSql('ALTER TABLE sub_task ALTER COLUMN is_default SET NOT NULL');
        $this->addSql('ALTER TABLE sub_task ALTER COLUMN is_default DROP DEFAULT');

        // Create archived_item table
        $this->addSql('CREATE TABLE archived_item (
            id SERIAL NOT NULL, 
            user_id INT NOT NULL,
            item_type VARCHAR(255) NOT NULL,
            item_id INT NOT NULL,
            archived_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_archived_item_user ON archived_item (user_id)');
        $this->addSql('ALTER TABLE archived_item ADD CONSTRAINT FK_archived_item_user_fk FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE archived_item');
        $this->addSql('ALTER TABLE task DROP COLUMN is_default');
        $this->addSql('ALTER TABLE sub_task DROP COLUMN is_default');
    }
}
