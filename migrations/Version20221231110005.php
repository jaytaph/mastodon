<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221231110005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ALTER source TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER fields TYPE JSON');
        $this->addSql('ALTER TABLE config ALTER status_length DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER media_attachments DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER characters_per_url DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER account_tags DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER options_per_poll DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER characers_per_option DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER minimum_poll_expiration DROP DEFAULT');
        $this->addSql('ALTER TABLE config ALTER maximum_poll_expiration DROP DEFAULT');
        $this->addSql('ALTER TABLE db_queue_entry ALTER data TYPE JSON');
        $this->addSql('ALTER TABLE db_queue_entry ALTER last_run_at DROP NOT NULL');
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE config ALTER status_length SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER media_attachments SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER characters_per_url SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER account_tags SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER options_per_poll SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER characers_per_option SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER minimum_poll_expiration SET DEFAULT 0');
        $this->addSql('ALTER TABLE config ALTER maximum_poll_expiration SET DEFAULT 0');
        $this->addSql('ALTER TABLE db_queue_entry ALTER data TYPE JSON');
        $this->addSql('ALTER TABLE db_queue_entry ALTER last_run_at SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE account ALTER source TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER fields TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
    }
}
