<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221208124451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added connection between polls and status';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE poll ADD status_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN poll.status_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE poll ADD CONSTRAINT FK_84BCFA456BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84BCFA456BF700BD ON poll (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE poll DROP CONSTRAINT FK_84BCFA456BF700BD');
        $this->addSql('DROP INDEX UNIQ_84BCFA456BF700BD');
        $this->addSql('ALTER TABLE poll DROP status_id');
    }
}
