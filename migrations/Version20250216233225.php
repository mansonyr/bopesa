<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216233225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel ADD explanation LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE sub_task ADD explanation LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD explanation LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel DROP explanation');
        $this->addSql('ALTER TABLE task DROP explanation');
        $this->addSql('ALTER TABLE sub_task DROP explanation');
    }
}
