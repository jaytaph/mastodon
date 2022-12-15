<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221213133710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialise tag history table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE tag_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE tag_history (id INT NOT NULL, name VARCHAR(255) NOT NULL, date DATE NOT NULL, uses INT NOT NULL, accounts INT NOT NULL, account VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE tag_history_id_seq CASCADE');
        $this->addSql('DROP TABLE tag_history');
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
