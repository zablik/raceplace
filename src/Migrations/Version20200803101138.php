<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200803101138 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE race ADD slug VARCHAR(255) NOT NULL, ADD results_source_type VARCHAR(255) NOT NULL, ADD results_source_link VARCHAR(255) NOT NULL, ADD results_source_checkpoints_link VARCHAR(255) NOT NULL, ADD results_source_codes VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event ADD slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE event DROP slug');
        $this->addSql('ALTER TABLE race DROP slug, DROP results_source_type, DROP results_source_link, DROP results_source_checkpoints_link, DROP results_source_codes');
    }
}
