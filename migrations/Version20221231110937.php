<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221231110937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added DC2Type hint for typearray';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ALTER source TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER fields TYPE JSON');
        $this->addSql('COMMENT ON COLUMN account.source IS \'(DC2Type:type_array)\'');
        $this->addSql('COMMENT ON COLUMN account.emojis IS \'(DC2Type:type_array)\'');
        $this->addSql('COMMENT ON COLUMN account.fields IS \'(DC2Type:type_array)\'');
        $this->addSql('ALTER TABLE db_queue_entry ALTER data TYPE JSON');
        $this->addSql('COMMENT ON COLUMN db_queue_entry.data IS \'(DC2Type:type_array)\'');
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
        $this->addSql('COMMENT ON COLUMN poll.options IS \'(DC2Type:type_array)\'');
        $this->addSql('COMMENT ON COLUMN poll.emojis IS \'(DC2Type:type_array)\'');
        $this->addSql('COMMENT ON COLUMN poll.votes IS \'(DC2Type:type_array)\'');
        $this->addSql('COMMENT ON COLUMN poll.own_votes IS \'(DC2Type:type_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE db_queue_entry ALTER data TYPE JSON');
        $this->addSql('COMMENT ON COLUMN db_queue_entry.data IS NULL');
        $this->addSql('ALTER TABLE account ALTER source TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER fields TYPE JSON');
        $this->addSql('COMMENT ON COLUMN account.source IS NULL');
        $this->addSql('COMMENT ON COLUMN account.emojis IS NULL');
        $this->addSql('COMMENT ON COLUMN account.fields IS NULL');
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
        $this->addSql('COMMENT ON COLUMN poll.options IS NULL');
        $this->addSql('COMMENT ON COLUMN poll.emojis IS NULL');
        $this->addSql('COMMENT ON COLUMN poll.votes IS NULL');
        $this->addSql('COMMENT ON COLUMN poll.own_votes IS NULL');
    }
}
