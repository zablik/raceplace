<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201114213341 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checkpoint ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE event ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE profile ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE profile_checkpoint ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE profile_result ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE race ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE results_source_table_config_type results_source_table_config_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE checkpoint DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE event DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE profile DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE profile_checkpoint DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE profile_result DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE race DROP created_at, DROP updated_at, CHANGE results_source_table_config_type results_source_table_config_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user DROP created_at, DROP updated_at, DROP deleted_at');
    }
}
