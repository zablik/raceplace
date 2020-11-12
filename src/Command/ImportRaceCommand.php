<?php

namespace App\Command;

use App\Service\Importer\DataProvider\Race\RaceDataProviderHub;
use App\Service\Importer\RaceImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @example php bin/console rp:import:race xcm-naliboki-2020
 */
class ImportRaceCommand extends Command
{
    protected static $defaultName = 'rp:import:race';

    protected RaceImporter $importer;
    protected LoggerInterface $logger;

    public function __construct(RaceImporter $importer, LoggerInterface $logger)
    {
        $this->importer = $importer;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports Event, Races and Checkpoints')
            ->addArgument('eventSlug', InputArgument::REQUIRED, 'Event slug')
            ->addArgument('source', InputArgument::OPTIONAL, 'Source', RaceDataProviderHub::YAML)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $eventSlug = $input->getArgument('eventSlug');
        $source = $input->getArgument('source');

        $io->title(sprintf('Import event "%s" with it\'s races and checkpoints from %s source', $eventSlug, $source));

        try {
            $this->importer->import($eventSlug, $source);
        } catch (\Exception $e) {
            $io->error('Command failed with an exception message: ' . $e->getMessage());
            $this->logger->error($e);
            return 1;
        }

        $io->success('Yoohoo! Event imported');

        return 0;
    }
}
