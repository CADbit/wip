<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102152421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id UUID NOT NULL, reserved_by VARCHAR(255) NOT NULL, period tstzrange NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, resource_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_42C8495589329D25 ON reservation (resource_id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495589329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE resource ALTER type TYPE VARCHAR');
        $this->addSql('ALTER TABLE resource ALTER status TYPE VARCHAR');
        $this->addSql('ALTER TABLE resource ALTER unavailability TYPE VARCHAR');
        $this->addSql('COMMENT ON COLUMN resource.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN resource.type IS \'\'');
        $this->addSql('COMMENT ON COLUMN resource.status IS \'\'');
        $this->addSql('COMMENT ON COLUMN resource.unavailability IS \'\'');
        $this->addSql('COMMENT ON COLUMN resource.created_at IS \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C8495589329D25');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('ALTER TABLE resource ALTER type TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE resource ALTER status TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE resource ALTER unavailability TYPE VARCHAR(255)');
        $this->addSql('COMMENT ON COLUMN resource.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN resource.type IS \'(DC2Type:resource_type)\'');
        $this->addSql('COMMENT ON COLUMN resource.status IS \'(DC2Type:resource_status)\'');
        $this->addSql('COMMENT ON COLUMN resource.unavailability IS \'(DC2Type:resource_unavailability)\'');
        $this->addSql('COMMENT ON COLUMN resource.created_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
