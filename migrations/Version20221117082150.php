<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117082150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added pub/priv key to account table';
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account ADD public_key_pem TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE account ADD private_key_pem TEXT DEFAULT NULL');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE account DROP public_key_pem');
        $this->addSql('ALTER TABLE account DROP private_key_pem');
    }
}
