<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128142203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'A status has a specific owner referenced in the account table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN status.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7B00651C7E3C61F9 ON status (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C7E3C61F9');
        $this->addSql('DROP INDEX IDX_7B00651C7E3C61F9');
        $this->addSql('ALTER TABLE status DROP owner_id');
    }
}
