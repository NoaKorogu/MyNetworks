<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250513064808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bus_stop (id INT NOT NULL, line_number VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE electrical (id INT NOT NULL, capacity VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE log (id SERIAL NOT NULL, user_id INT NOT NULL, table_name VARCHAR(50) NOT NULL, id_element INT NOT NULL, old_data JSON NOT NULL, new_data JSON NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F3F68C5A76ED395 ON log (user_id)');
        $this->addSql('CREATE TABLE network (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE part_of (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE path_part_of (path_id INT NOT NULL, part_of_id INT NOT NULL, PRIMARY KEY(path_id, part_of_id))');
        $this->addSql('CREATE INDEX IDX_3301BB6FD96C566B ON path_part_of (path_id)');
        $this->addSql('CREATE INDEX IDX_3301BB6FC97EF49F ON path_part_of (part_of_id)');
        $this->addSql('CREATE TABLE structure_part_of (structure_id INT NOT NULL, part_of_id INT NOT NULL, PRIMARY KEY(structure_id, part_of_id))');
        $this->addSql('CREATE INDEX IDX_2F15FD772534008B ON structure_part_of (structure_id)');
        $this->addSql('CREATE INDEX IDX_2F15FD77C97EF49F ON structure_part_of (part_of_id)');
        $this->addSql('CREATE TABLE path (id SERIAL NOT NULL, network_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, path geometry(LINESTRING, 0) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B548B0F34128B91 ON path (network_id)');
        $this->addSql('CREATE TABLE structure (id SERIAL NOT NULL, network_id INT NOT NULL, type_id INT NOT NULL, name VARCHAR(128) NOT NULL, location Geography NOT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6F0137EA34128B91 ON structure (network_id)');
        $this->addSql('CREATE INDEX IDX_6F0137EAC54C8C93 ON structure (type_id)');
        $this->addSql('COMMENT ON COLUMN structure.location IS \'(DC2Type:geography)\'');
        $this->addSql('CREATE TABLE type (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE TABLE water (id INT NOT NULL, water_pressure VARCHAR(50) DEFAULT NULL, is_open BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE bus_stop ADD CONSTRAINT FK_E65B69FCBF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE electrical ADD CONSTRAINT FK_6BBA6B48BF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE path_part_of ADD CONSTRAINT FK_3301BB6FD96C566B FOREIGN KEY (path_id) REFERENCES part_of (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE path_part_of ADD CONSTRAINT FK_3301BB6FC97EF49F FOREIGN KEY (part_of_id) REFERENCES path (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure_part_of ADD CONSTRAINT FK_2F15FD772534008B FOREIGN KEY (structure_id) REFERENCES part_of (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure_part_of ADD CONSTRAINT FK_2F15FD77C97EF49F FOREIGN KEY (part_of_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE path ADD CONSTRAINT FK_B548B0F34128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA34128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE water ADD CONSTRAINT FK_FB3314DABF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bus_stop DROP CONSTRAINT FK_E65B69FCBF396750');
        $this->addSql('ALTER TABLE electrical DROP CONSTRAINT FK_6BBA6B48BF396750');
        $this->addSql('ALTER TABLE log DROP CONSTRAINT FK_8F3F68C5A76ED395');
        $this->addSql('ALTER TABLE path_part_of DROP CONSTRAINT FK_3301BB6FD96C566B');
        $this->addSql('ALTER TABLE path_part_of DROP CONSTRAINT FK_3301BB6FC97EF49F');
        $this->addSql('ALTER TABLE structure_part_of DROP CONSTRAINT FK_2F15FD772534008B');
        $this->addSql('ALTER TABLE structure_part_of DROP CONSTRAINT FK_2F15FD77C97EF49F');
        $this->addSql('ALTER TABLE path DROP CONSTRAINT FK_B548B0F34128B91');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA34128B91');
        $this->addSql('ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAC54C8C93');
        $this->addSql('ALTER TABLE water DROP CONSTRAINT FK_FB3314DABF396750');
        $this->addSql('DROP TABLE bus_stop');
        $this->addSql('DROP TABLE electrical');
        $this->addSql('DROP TABLE log');
        $this->addSql('DROP TABLE network');
        $this->addSql('DROP TABLE part_of');
        $this->addSql('DROP TABLE path_part_of');
        $this->addSql('DROP TABLE structure_part_of');
        $this->addSql('DROP TABLE path');
        $this->addSql('DROP TABLE structure');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE water');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
