<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507115230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bus_stop (id INT NOT NULL, line_number VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE electrical (id INT NOT NULL, capacity VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE network (id SERIAL NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE part_of (id SERIAL NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE path_part_of (path_id INT NOT NULL, part_of_id INT NOT NULL, PRIMARY KEY(path_id, part_of_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3301BB6FD96C566B ON path_part_of (path_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3301BB6FC97EF49F ON path_part_of (part_of_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE structure_part_of (structure_id INT NOT NULL, part_of_id INT NOT NULL, PRIMARY KEY(structure_id, part_of_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2F15FD772534008B ON structure_part_of (structure_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2F15FD77C97EF49F ON structure_part_of (part_of_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE path (id SERIAL NOT NULL, network_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, path geometry(LINESTRING, 0) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B548B0F34128B91 ON path (network_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE structure (id SERIAL NOT NULL, network_id INT NOT NULL, type_id INT NOT NULL, created_by_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(128) NOT NULL, location geometry(POINT, 4326) DEFAULT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6F0137EA34128B91 ON structure (network_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6F0137EAC54C8C93 ON structure (type_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6F0137EAB03A8386 ON structure (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type (id SERIAL NOT NULL, network_id INT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8CDE572934128B91 ON type (network_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id SERIAL NOT NULL, network_id INT NOT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D64934128B91 ON "user" (network_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE water (id INT NOT NULL, water_pressure VARCHAR(50) DEFAULT NULL, is_open BOOLEAN DEFAULT true, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bus_stop ADD CONSTRAINT FK_E65B69FCBF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE electrical ADD CONSTRAINT FK_6BBA6B48BF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path_part_of ADD CONSTRAINT FK_3301BB6FD96C566B FOREIGN KEY (path_id) REFERENCES part_of (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path_part_of ADD CONSTRAINT FK_3301BB6FC97EF49F FOREIGN KEY (part_of_id) REFERENCES path (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure_part_of ADD CONSTRAINT FK_2F15FD772534008B FOREIGN KEY (structure_id) REFERENCES part_of (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure_part_of ADD CONSTRAINT FK_2F15FD77C97EF49F FOREIGN KEY (part_of_id) REFERENCES structure (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path ADD CONSTRAINT FK_B548B0F34128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure ADD CONSTRAINT FK_6F0137EA34128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure ADD CONSTRAINT FK_6F0137EAB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type ADD CONSTRAINT FK_8CDE572934128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64934128B91 FOREIGN KEY (network_id) REFERENCES network (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE water ADD CONSTRAINT FK_FB3314DABF396750 FOREIGN KEY (id) REFERENCES structure (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bus_stop DROP CONSTRAINT FK_E65B69FCBF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE electrical DROP CONSTRAINT FK_6BBA6B48BF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path_part_of DROP CONSTRAINT FK_3301BB6FD96C566B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path_part_of DROP CONSTRAINT FK_3301BB6FC97EF49F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure_part_of DROP CONSTRAINT FK_2F15FD772534008B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure_part_of DROP CONSTRAINT FK_2F15FD77C97EF49F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE path DROP CONSTRAINT FK_B548B0F34128B91
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure DROP CONSTRAINT FK_6F0137EA34128B91
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAC54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE structure DROP CONSTRAINT FK_6F0137EAB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type DROP CONSTRAINT FK_8CDE572934128B91
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64934128B91
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE water DROP CONSTRAINT FK_FB3314DABF396750
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bus_stop
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE electrical
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE network
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE part_of
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE path_part_of
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE structure_part_of
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE path
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE structure
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE water
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
