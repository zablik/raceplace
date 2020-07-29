<?php

namespace App\Command;

use App\Service\ResultsManager\RaceImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportRaceResultsCommand extends Command
{
    protected static $defaultName = 'rp:import:race-results';

    protected RaceImporter $importer;

    public function __construct(RaceImporter $importer)
    {
        $this->importer = $importer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Parse web page, Imports race results')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        $this->importer->import();





        /*
        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        */

        return 0;
    }
}
