<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221206152136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping some NOT NULLS in the status table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER attachment_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER tag_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER mention_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER emoji_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status ALTER owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER attachment_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER tag_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER mention_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER emoji_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER in_reply_to_uri SET NOT NULL');
    }
}
