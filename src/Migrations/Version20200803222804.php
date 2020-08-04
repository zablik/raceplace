<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200803222804 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

//        $this->addSql('CREATE INDEX type ON race (type)');
//        $this->addSql('CREATE INDEX race_slug ON race (slug)');
//        $this->addSql('CREATE INDEX distance ON race (distance)');
        //$this->addSql('CREATE UNIQUE INDEX slug ON race (slug, event_id)');
        $this->addSql('CREATE INDEX `group` ON profile (`group`)');
        $this->addSql('CREATE INDEX name ON profile (name)');
        $this->addSql('CREATE INDEX birthday ON profile (birthday)');
        $this->addSql('CREATE INDEX date ON event (date)');
        $this->addSql('CREATE UNIQUE INDEX slug ON event (slug)');
        $this->addSql('CREATE INDEX numberPlate ON profile_result (number_plate)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX date ON event');
        $this->addSql('DROP INDEX slug ON event');
        $this->addSql('DROP INDEX `group` ON profile');
        $this->addSql('DROP INDEX name ON profile');
        $this->addSql('DROP INDEX birthday ON profile');
        $this->addSql('DROP INDEX numberPlate ON profile_result');
        $this->addSql('DROP INDEX type ON race');
        $this->addSql('DROP INDEX race_slug ON race');
        $this->addSql('DROP INDEX distance ON race');
        $this->addSql('DROP INDEX slug ON race');
    }
}
