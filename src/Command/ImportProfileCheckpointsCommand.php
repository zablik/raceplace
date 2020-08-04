<?php

namespace App\Command;

use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\Importer\ProfileCheckpointsImporter;
use App\Service\Importer\RaceResultsImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProfileCheckpointsCommand extends Command
{
    protected static $defaultName = 'rp:import:profile-checkpoints';

    protected ProfileCheckpointsImporter $importer;
    protected LoggerInterface $logger;

    public function __construct(ProfileCheckpointsImporter $importer, LoggerInterface $logger)
    {
        $this->importer = $importer;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports checkpoints pass times of the races for the particular event')
            ->addArgument('eventSlug', InputArgument::REQUIRED, 'Event slug')
            ->addArgument('source', InputArgument::OPTIONAL, 'Source', ProfileDataProviderHub::OBELARUS)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $eventSlug = $input->getArgument('eventSlug');
        $source = $input->getArgument('source');

        $io->title(sprintf('Importing race results that participated "%s" event from %s source', $eventSlug, $source));

        try {
            $this->importer->import($eventSlug, $source);
        } catch (\Exception $e) {
            $io->error('Command failed with an exception message: ' . $e->getMessage());
            $this->logger->error(sprintf(
                '%s: %s. Trace: %s',
                self::$defaultName,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
            return 1;
        }

        $io->success('Yoohoo! Race profile checkpoints imported');

        return 0;
    }
}
