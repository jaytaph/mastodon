<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221123083740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE status (id UUID NOT NULL, account_id UUID DEFAULT NULL, in_reply_to_id UUID DEFAULT NULL, in_reply_to_account_id UUID DEFAULT NULL, boost_of_id UUID DEFAULT NULL, boost_of_account_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, uri TEXT NOT NULL, url TEXT NOT NULL, content TEXT NOT NULL, attachment_ids JSON NOT NULL, tag_ids JSON NOT NULL, mention_ids JSON NOT NULL, emoji_ids JSON NOT NULL, local BOOLEAN NOT NULL, account_uri TEXT NOT NULL, in_reply_to_uri TEXT NOT NULL, content_warning TEXT NOT NULL, visibility VARCHAR(255) NOT NULL, sensitive BOOLEAN NOT NULL, language VARCHAR(255) NOT NULL, created_with_application_id VARCHAR(255) NOT NULL, activity_streams_type VARCHAR(255) NOT NULL, text TEXT NOT NULL, pinned BOOLEAN NOT NULL, federated BOOLEAN NOT NULL, boostable BOOLEAN NOT NULL, replyable BOOLEAN NOT NULL, likable BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7B00651C9B6B5FBA ON status (account_id)');
        $this->addSql('CREATE INDEX IDX_7B00651CDD92DAB8 ON status (in_reply_to_id)');
        $this->addSql('CREATE INDEX IDX_7B00651C1AD58A0E ON status (in_reply_to_account_id)');
        $this->addSql('CREATE INDEX IDX_7B00651C9B060395 ON status (boost_of_id)');
        $this->addSql('CREATE INDEX IDX_7B00651C17B942A3 ON status (boost_of_account_id)');
        $this->addSql('COMMENT ON COLUMN status.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN status.account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN status.in_reply_to_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN status.in_reply_to_account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN status.boost_of_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN status.boost_of_account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651CDD92DAB8 FOREIGN KEY (in_reply_to_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C1AD58A0E FOREIGN KEY (in_reply_to_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C9B060395 FOREIGN KEY (boost_of_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C17B942A3 FOREIGN KEY (boost_of_account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C9B6B5FBA');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651CDD92DAB8');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C1AD58A0E');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C9B060395');
        $this->addSql('ALTER TABLE status DROP CONSTRAINT FK_7B00651C17B942A3');
        $this->addSql('DROP TABLE status');
    }
}
