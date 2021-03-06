<?php

namespace App\Command;

use App\Entity\Race;
use App\Service\Importer\RaceResultsImporter;
use App\Service\Rating\ResultRatioManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportRaceResultsCommand extends Command
{
    protected static $defaultName = 'rp:import:race:results:import';

    protected ResultRatioManager $ratioManager;
    protected EntityManagerInterface $em;
    protected RaceResultsImporter $importer;

    public function __construct(EntityManagerInterface $em, RaceResultsImporter $importer)
    {
        $this->importer = $importer;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Imports Results for Races')
            ->addArgument('raceIds', InputArgument::IS_ARRAY, 'Race IDs', [])
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        ini_set('memory_limit', '1500M');
//        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $io = new SymfonyStyle($input, $output);

        $raceIds = $input->getArgument('raceIds');
        array_walk($raceIds, 'intval');

        $overwrite = (bool)$input->getOption('force');

        $raceRepository = $this->em->getRepository(Race::class);
        $racesCount = $raceRepository->getIterationCount($raceIds);
        $iterableResult = $raceRepository->getIterationFindQuery($raceIds)->iterate();

        $i = 1;

        while (($res = $iterableResult->next()) !== false) {
            /** @var Race $race */
            $race= $res[0];

            if (0 !== $race->getProfileResults()->count()) {
                if ($overwrite) {
                    $io->note(sprintf('Overwriting results for Race #%d', $race->getId()));
                } else {
                    $io->warning(sprintf('Results for Race #%d was already imported. Skipping', $race->getId()));

                    continue;
                }
            }

            $this->importer->deleteRaceResults($race);
            $this->em->flush();

            $this->importer->importRaceResults($race);

            $this->em->flush();
            $this->em->clear();

            $io->note(sprintf('%d (%d): #%d [memory=%d MB]. Imported %d race results', $i++,  $racesCount, $race->getId(), (memory_get_usage(1) / 1024 / 1024), $race->getProfileResults()->count()));
        }

        $io->success('Yeeees');

        return Command::SUCCESS;
    }
}
