<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200805212506 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile_checkpoint DROP INDEX UNIQ_C86BC0E3F27C615F, ADD INDEX IDX_C86BC0E3F27C615F (checkpoint_id)');
        $this->addSql('ALTER TABLE profile_checkpoint CHANGE checkpoint_id checkpoint_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile_checkpoint DROP INDEX IDX_C86BC0E3F27C615F, ADD UNIQUE INDEX UNIQ_C86BC0E3F27C615F (checkpoint_id)');
        $this->addSql('ALTER TABLE profile_checkpoint CHANGE checkpoint_id checkpoint_id INT NOT NULL');
    }
}
