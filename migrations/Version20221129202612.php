<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221129202612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Status.visibility is a string, not bool';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ALTER owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE status ALTER visibility TYPE VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status ALTER owner_id DROP NOT NULL');
        $this->addSql('ALTER TABLE status ALTER visibility TYPE BOOLEAN');
    }
}
