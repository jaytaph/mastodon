<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221208122407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial Poll table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE poll (id UUID NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expired BOOLEAN NOT NULL, multiple BOOLEAN NOT NULL, votes_count INT NOT NULL, voters_count INT NOT NULL, options JSON NOT NULL, emojis JSON NOT NULL, votes JSON NOT NULL, own_votes JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN poll.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN poll.expires_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE poll');
    }
}
