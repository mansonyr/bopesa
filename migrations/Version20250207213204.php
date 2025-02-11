<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207213204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE sub_task_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE task_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE sub_task (id INT NOT NULL, task_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, resources TEXT DEFAULT NULL, is_completed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75E844E48DB60186 ON sub_task (task_id)');
        $this->addSql('CREATE TABLE task (id INT NOT NULL, channel_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, status VARCHAR(20) NOT NULL, progress DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_527EDB2572F5A1AA ON task (channel_id)');
        $this->addSql('ALTER TABLE sub_task ADD CONSTRAINT FK_75E844E48DB60186 FOREIGN KEY (task_id) REFERENCES task (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2572F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE sub_task_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE task_id_seq CASCADE');
        $this->addSql('ALTER TABLE sub_task DROP CONSTRAINT FK_75E844E48DB60186');
        $this->addSql('ALTER TABLE task DROP CONSTRAINT FK_527EDB2572F5A1AA');
        $this->addSql('DROP TABLE sub_task');
        $this->addSql('DROP TABLE task');
    }
}
