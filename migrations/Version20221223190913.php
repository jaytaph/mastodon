<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221223190913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Initial Queue Schema';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE db_queue_entry (id UUID NOT NULL, type VARCHAR(255) NOT NULL, data JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_run_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, attempts INT NOT NULL, status VARCHAR(255) NOT NULL, failed TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN db_queue_entry.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN db_queue_entry.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN db_queue_entry.last_run_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE db_queue_entry');
    }
}
