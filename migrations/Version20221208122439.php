<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221208122439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed some fields in Status table to NOT NULL';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ALTER attachment_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER tag_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER mention_ids SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER emoji_ids SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status ALTER attachment_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER tag_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER mention_ids DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER emoji_ids DROP NOT NULL');
    }
}
