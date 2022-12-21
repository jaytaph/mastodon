<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221221123134 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config ADD status_length INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD media_attachments INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD characters_per_url INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD account_tags INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD options_per_poll INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD characers_per_option INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD minimum_poll_expiration INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE config ADD maximum_poll_expiration INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE config DROP status_length');
        $this->addSql('ALTER TABLE config DROP media_attachments');
        $this->addSql('ALTER TABLE config DROP characters_per_url');
        $this->addSql('ALTER TABLE config DROP account_tags');
        $this->addSql('ALTER TABLE config DROP options_per_poll');
        $this->addSql('ALTER TABLE config DROP characers_per_option');
        $this->addSql('ALTER TABLE config DROP minimum_poll_expiration');
        $this->addSql('ALTER TABLE config DROP maximum_poll_expiration');
    }
}
