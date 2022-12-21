<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221220185552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial Config table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE config (id UUID NOT NULL, instance_domain VARCHAR(255) NOT NULL, instance_title VARCHAR(255) NOT NULL, instance_description TEXT NOT NULL, instance_short_description VARCHAR(255) NOT NULL, instance_email VARCHAR(255) NOT NULL, languages JSON NOT NULL, registration_allowed BOOLEAN NOT NULL, approval_required BOOLEAN NOT NULL, invite_enabled BOOLEAN NOT NULL, thumbnail_url VARCHAR(255) NOT NULL, admin_account VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN config.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE config');
    }
}
