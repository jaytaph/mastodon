<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117083917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Account id is a UUID';
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ALTER id TYPE UUID USING id::uuid');
        $this->addSql('COMMENT ON COLUMN account.id IS \'(DC2Type:uuid)\'');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account ALTER id TYPE UUID');
        $this->addSql('COMMENT ON COLUMN account.id IS NULL');
    }
}
