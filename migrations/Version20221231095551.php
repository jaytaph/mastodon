<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221231095551 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added addressing to status';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ADD _to JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD bto JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD cc JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD bcc JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE status DROP _to');
        $this->addSql('ALTER TABLE status DROP bto');
        $this->addSql('ALTER TABLE status DROP cc');
        $this->addSql('ALTER TABLE status DROP bcc');
    }
}
