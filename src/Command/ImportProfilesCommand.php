<?php

namespace App\Command;

use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\Importer\ProfileImporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportProfilesCommand extends Command
{
    protected static $defaultName = 'rp:import:profiles';

    protected ProfileImporter $importer;
    protected LoggerInterface $logger;

    public function __construct(ProfileImporter $importer, LoggerInterface $logger)
    {
        $this->importer = $importer;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports profiles for the particular event')
            ->addArgument('eventSlug', InputArgument::REQUIRED, 'Event slug')
            ->addArgument('source', InputArgument::OPTIONAL, 'Source', ProfileDataProviderHub::OBELARUS)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $eventSlug = $input->getArgument('eventSlug');
        $source = $input->getArgument('source');

        $io->title(sprintf('Importing profiles that participated "%s" event from %s source', $eventSlug, $source));

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

        $io->success('Yoohoo! Profiles imported');

        return 0;
    }
}
