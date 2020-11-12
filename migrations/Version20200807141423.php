<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200807141423 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE profile_checkpoint (id INT AUTO_INCREMENT NOT NULL, checkpoint_id INT DEFAULT NULL, profile_result_id INT DEFAULT NULL, time TIME NOT NULL, total_time TIME NOT NULL, speed DOUBLE PRECISION NOT NULL, pace TIME NOT NULL, INDEX IDX_C86BC0E3F27C615F (checkpoint_id), INDEX IDX_C86BC0E3BB385F38 (profile_result_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, distance DOUBLE PRECISION DEFAULT NULL, slug VARCHAR(255) NOT NULL, results_source_type VARCHAR(255) NOT NULL, results_source_link VARCHAR(255) NOT NULL, results_source_checkpoints_link VARCHAR(255) DEFAULT NULL, results_source_codes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_DA6FBBAF71F7E88B (event_id), INDEX type (type), INDEX race_slug (slug), INDEX distance (distance), UNIQUE INDEX race_event_slug (slug, event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, birthday DATE DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, club VARCHAR(255) DEFAULT NULL, `group` VARCHAR(255) DEFAULT NULL, strava_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8157AA0FA76ED395 (user_id), INDEX `group` (`group`), INDEX name (name), INDEX birthday (birthday), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, date DATE NOT NULL, slug VARCHAR(255) NOT NULL, INDEX date (date), UNIQUE INDEX event_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkpoint (id INT AUTO_INCREMENT NOT NULL, race_id INT NOT NULL, distance DOUBLE PRECISION DEFAULT NULL, mark VARCHAR(255) DEFAULT NULL, INDEX IDX_F00F7BE6E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_result (id INT AUTO_INCREMENT NOT NULL, race_id INT DEFAULT NULL, profile_id INT DEFAULT NULL, time TIME DEFAULT NULL, place INT DEFAULT NULL, disqualification VARCHAR(255) DEFAULT NULL, number_plate VARCHAR(255) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, INDEX IDX_7B40A6746E59D40D (race_id), INDEX IDX_7B40A674CCFA12B8 (profile_id), INDEX numberPlate (number_plate), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE profile_checkpoint ADD CONSTRAINT FK_C86BC0E3F27C615F FOREIGN KEY (checkpoint_id) REFERENCES checkpoint (id)');
        $this->addSql('ALTER TABLE profile_checkpoint ADD CONSTRAINT FK_C86BC0E3BB385F38 FOREIGN KEY (profile_result_id) REFERENCES profile_result (id)');
        $this->addSql('ALTER TABLE race ADD CONSTRAINT FK_DA6FBBAF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE checkpoint ADD CONSTRAINT FK_F00F7BE6E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE profile_result ADD CONSTRAINT FK_7B40A6746E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE profile_result ADD CONSTRAINT FK_7B40A674CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE checkpoint DROP FOREIGN KEY FK_F00F7BE6E59D40D');
        $this->addSql('ALTER TABLE profile_result DROP FOREIGN KEY FK_7B40A6746E59D40D');
        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FA76ED395');
        $this->addSql('ALTER TABLE profile_result DROP FOREIGN KEY FK_7B40A674CCFA12B8');
        $this->addSql('ALTER TABLE race DROP FOREIGN KEY FK_DA6FBBAF71F7E88B');
        $this->addSql('ALTER TABLE profile_checkpoint DROP FOREIGN KEY FK_C86BC0E3F27C615F');
        $this->addSql('ALTER TABLE profile_checkpoint DROP FOREIGN KEY FK_C86BC0E3BB385F38');
        $this->addSql('DROP TABLE profile_checkpoint');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE checkpoint');
        $this->addSql('DROP TABLE profile_result');
    }
}
