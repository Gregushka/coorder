<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251224193940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE video_file_data DROP CONSTRAINT fk_e670e6982b1b1c69');
        $this->addSql('DROP TABLE video_file_data');
        $this->addSql('ALTER TABLE attached_file ADD file_info JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE attached_file ADD service_info JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE exif_data_entry DROP CONSTRAINT fk_5c075cd9cd6009c8');
        $this->addSql('DROP INDEX idx_5c075cd9cd6009c8');
        $this->addSql('ALTER TABLE exif_data_entry DROP video_file_data_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE video_file_data (id UUID NOT NULL, video_file_name VARCHAR(255) NOT NULL, video_file_xml JSON NOT NULL, exif_header JSON NOT NULL, exif_parameters JSON NOT NULL, logfile_name VARCHAR(255) NOT NULL, logfile_header JSON NOT NULL, thumbnail VARCHAR(255) NOT NULL, heavy_name VARCHAR(36) NOT NULL, permissions JSON NOT NULL, media_card_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_e670e6982b1b1c69 ON video_file_data (media_card_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_e670e6984dbe0824 ON video_file_data (heavy_name)');
        $this->addSql('ALTER TABLE video_file_data ADD CONSTRAINT fk_e670e6982b1b1c69 FOREIGN KEY (media_card_id) REFERENCES media_card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE attached_file DROP file_info');
        $this->addSql('ALTER TABLE attached_file DROP service_info');
        $this->addSql('ALTER TABLE exif_data_entry ADD video_file_data_id UUID NOT NULL');
        $this->addSql('ALTER TABLE exif_data_entry ADD CONSTRAINT fk_5c075cd9cd6009c8 FOREIGN KEY (video_file_data_id) REFERENCES video_file_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5c075cd9cd6009c8 ON exif_data_entry (video_file_data_id)');
    }
}
