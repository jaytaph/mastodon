<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221127100922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial media attachment table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media_attachment (id UUID NOT NULL, type VARCHAR(255) NOT NULL, url TEXT NOT NULL, preview_url TEXT NOT NULL, text_url TEXT NOT NULL, remote_url TEXT NOT NULL, description TEXT NOT NULL, blurhash VARCHAR(255) NOT NULL, meta JSON NOT NULL, filename VARCHAR(255) NOT NULL, focus JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN media_attachment.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE media_attachment');
    }
}
