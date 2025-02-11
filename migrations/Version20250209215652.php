<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209215652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ajouter last_name comme nullable pour l'instant
        $this->addSql('ALTER TABLE "user" ADD last_name VARCHAR(255)');
        
        // Renommer full_name en first_name
        $this->addSql('ALTER TABLE "user" RENAME COLUMN full_name TO first_name');
        
        // Mettre à jour les utilisateurs existants
        $this->addSql('UPDATE "user" SET last_name = first_name WHERE last_name IS NULL');
        
        // Rendre last_name non nullable
        $this->addSql('ALTER TABLE "user" ALTER COLUMN last_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" ADD full_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP first_name');
        $this->addSql('ALTER TABLE "user" DROP last_name');
    }
}
