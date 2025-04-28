<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250317103311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT fk_6f0137eab15e270b');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT fk_6f0137ea714819a0');
        $this->addSql('DROP INDEX idx_6f0137ea714819a0');
        $this->addSql('DROP INDEX idx_6f0137eab15e270b');
        $this->addSql('ALTER TABLE structure ADD network_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure DROP network_id_id');
        $this->addSql('ALTER TABLE structure DROP type_id_id');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA34128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F0137EA34128B91 ON structure (network_id)');
        $this->addSql('CREATE INDEX IDX_6F0137EAC54C8C93 ON structure (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA34128B91');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAC54C8C93');
        $this->addSql('DROP INDEX IDX_6F0137EA34128B91');
        $this->addSql('DROP INDEX IDX_6F0137EAC54C8C93');
        $this->addSql('ALTER TABLE structure ADD network_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure ADD type_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure DROP network_id');
        $this->addSql('ALTER TABLE structure DROP type_id');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT fk_6f0137eab15e270b FOREIGN KEY (network_id_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT fk_6f0137ea714819a0 FOREIGN KEY (type_id_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6f0137ea714819a0 ON structure (type_id_id)');
        $this->addSql('CREATE INDEX idx_6f0137eab15e270b ON structure (network_id_id)');
    }
}
