<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250209230701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Retour à un champ fullName unique';
    }

    public function up(Schema $schema): void
    {
        // Ajouter fullName comme nullable pour l'instant
        $this->addSql('ALTER TABLE "user" ADD full_name VARCHAR(255)');
        
        // Copier first_name et last_name dans full_name
        $this->addSql('UPDATE "user" SET full_name = CONCAT(first_name, \' \', last_name)');
        
        // Rendre full_name non nullable
        $this->addSql('ALTER TABLE "user" ALTER COLUMN full_name SET NOT NULL');
        
        // Supprimer les anciennes colonnes
        $this->addSql('ALTER TABLE "user" DROP first_name');
        $this->addSql('ALTER TABLE "user" DROP last_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD first_name VARCHAR(255)');
        $this->addSql('ALTER TABLE "user" ADD last_name VARCHAR(255)');
        $this->addSql('UPDATE "user" SET first_name = split_part(full_name, \' \', 1), last_name = split_part(full_name, \' \', 2)');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN first_name SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN last_name SET NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP full_name');
    }
}
