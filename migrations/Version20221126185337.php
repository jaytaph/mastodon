<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221126185337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updated follower table to use UUIDs';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follower ALTER id TYPE UUID USING id::uuid');
        $this->addSql('ALTER TABLE follower ALTER user_id TYPE UUID USING id::uuid');
        $this->addSql('ALTER TABLE follower ALTER follow_id TYPE UUID USING id::uuid');
        $this->addSql('COMMENT ON COLUMN follower.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN follower.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN follower.follow_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D60946A76ED395 FOREIGN KEY (user_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follower ADD CONSTRAINT FK_B9D609468711D3BC FOREIGN KEY (follow_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B9D60946A76ED395 ON follower (user_id)');
        $this->addSql('CREATE INDEX IDX_B9D609468711D3BC ON follower (follow_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE follower DROP CONSTRAINT FK_B9D60946A76ED395');
        $this->addSql('ALTER TABLE follower DROP CONSTRAINT FK_B9D609468711D3BC');
        $this->addSql('DROP INDEX IDX_B9D60946A76ED395');
        $this->addSql('DROP INDEX IDX_B9D609468711D3BC');
        $this->addSql('ALTER TABLE follower ALTER id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE follower ALTER user_id TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE follower ALTER follow_id TYPE VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN follower.id IS NULL');
        $this->addSql('COMMENT ON COLUMN follower.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN follower.follow_id IS NULL');
    }
}
