<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250515172907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d649b15e270b');
        $this->addSql('DROP INDEX idx_8d93d649b15e270b');
        $this->addSql('ALTER TABLE "user" ADD network_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" DROP network_id_id');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64934128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_8D93D64934128B91 ON "user" (network_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64934128B91');
        $this->addSql('DROP INDEX IDX_8D93D64934128B91');
        $this->addSql('ALTER TABLE "user" ADD network_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP network_id');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d649b15e270b FOREIGN KEY (network_id_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_8d93d649b15e270b ON "user" (network_id_id)');
    }
}
