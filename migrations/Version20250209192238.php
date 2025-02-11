<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209192238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Mettre à jour les enregistrements existants avec is_active = true
        $this->addSql('UPDATE channel SET is_active = true WHERE is_active IS NULL');
        $this->addSql('ALTER TABLE channel ALTER is_active SET DEFAULT true');
        $this->addSql('ALTER TABLE channel ALTER is_default DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE channel ALTER is_default SET DEFAULT false');
    }
}
