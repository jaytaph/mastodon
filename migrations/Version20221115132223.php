<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221115132223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id UUID NOT NULL, username VARCHAR(255) NOT NULL, acct VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, locked BOOLEAN NOT NULL, bot BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_status_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, note TEXT NOT NULL, url VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, avatar_static VARCHAR(255) NOT NULL, header VARCHAR(255) NOT NULL, header_static VARCHAR(255) NOT NULL, source JSON NOT NULL, emojis JSON NOT NULL, fields JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN account.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN account.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN account.last_status_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE account');
    }
}
