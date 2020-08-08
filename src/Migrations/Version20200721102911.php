<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @todo Move filesystem logic to build deploy commands
 *
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200721102911 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create directory to store HTML pages of race results [data/race_results]';
    }

    public function up(Schema $schema) : void
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir(__DIR__ . '/../../data/race_results');
    }

    public function down(Schema $schema) : void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/../../data');
    }
}
