<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221213134028 extends AbstractMigration
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
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
        $this->addSql('ALTER TABLE status ALTER owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri SET DEFAULT \'\'');
        $this->addSql('UPDATE status SET in_reply_to_uri = \'\' WHERE in_reply_to_uri IS NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE poll ALTER options TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER votes TYPE JSON');
        $this->addSql('ALTER TABLE poll ALTER own_votes TYPE JSON');
        $this->addSql('ALTER TABLE status ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri DROP NOT NULL');
        $this->addSql('ALTER TABLE account ALTER source TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER emojis TYPE JSON');
        $this->addSql('ALTER TABLE account ALTER fields TYPE JSON');
    }
}
