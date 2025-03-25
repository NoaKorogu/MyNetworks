<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325143459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT fk_6f0137ea34128b91');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT fk_6f0137eac54c8c93');
        $this->addSql('DROP INDEX idx_6f0137eac54c8c93');
        $this->addSql('DROP INDEX idx_6f0137ea34128b91');
        $this->addSql('ALTER TABLE structure ADD network_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure ADD type_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure DROP network_id');
        $this->addSql('ALTER TABLE structure DROP type_id');
        $this->addSql('ALTER TABLE structure ALTER location TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAB15E270B FOREIGN KEY (network_id_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA714819A0 FOREIGN KEY (type_id_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6F0137EAB15E270B ON structure (network_id_id)');
        $this->addSql('CREATE INDEX IDX_6F0137EA714819A0 ON structure (type_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAB15E270B');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA714819A0');
        $this->addSql('DROP INDEX IDX_6F0137EAB15E270B');
        $this->addSql('DROP INDEX IDX_6F0137EA714819A0');
        $this->addSql('ALTER TABLE structure ADD network_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure ADD type_id INT NOT NULL');
        $this->addSql('ALTER TABLE structure DROP network_id_id');
        $this->addSql('ALTER TABLE structure DROP type_id_id');
        $this->addSql('ALTER TABLE structure ALTER location TYPE geography(GEOMETRY, 4326)');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT fk_6f0137ea34128b91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT fk_6f0137eac54c8c93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_6f0137eac54c8c93 ON structure (type_id)');
        $this->addSql('CREATE INDEX idx_6f0137ea34128b91 ON structure (network_id)');
    }
}
