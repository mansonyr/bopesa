<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211193911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE channel ADD CONSTRAINT FK_A2F98E47A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A2F98E47A76ED395 ON channel (user_id)');
        $this->addSql('ALTER TABLE sub_task ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sub_task ALTER progress DROP DEFAULT');
        $this->addSql('ALTER TABLE sub_task ADD CONSTRAINT FK_75E844E4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_75E844E4A76ED395 ON sub_task (user_id)');
        $this->addSql('ALTER TABLE task ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE channel DROP CONSTRAINT FK_A2F98E47A76ED395');
        $this->addSql('DROP INDEX IDX_A2F98E47A76ED395');
        $this->addSql('ALTER TABLE channel DROP user_id');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB25A76ED395');
        $this->addSql('DROP INDEX IDX_527EDB25A76ED395');
        $this->addSql('ALTER TABLE task DROP user_id');
        $this->addSql('ALTER TABLE sub_task DROP CONSTRAINT FK_75E844E4A76ED395');
        $this->addSql('DROP INDEX IDX_75E844E4A76ED395');
        $this->addSql('ALTER TABLE sub_task DROP user_id');
        $this->addSql('ALTER TABLE sub_task ALTER progress SET DEFAULT 0');
    }
}
