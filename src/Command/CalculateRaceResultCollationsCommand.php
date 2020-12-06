<?php

namespace App\Command;

use App\Entity\Race;
use App\Entity\RaceResultCollation;
use App\Service\Rating\RaceResultCollationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CalculateRaceResultCollationsCommand extends Command
{
    protected static $defaultName = 'rp:calculate:result-collations';

    protected RaceResultCollationManager $collationManager;
    protected EntityManagerInterface $em;

    public function __construct(RaceResultCollationManager $collationManager, EntityManagerInterface $em)
    {
        $this->collationManager = $collationManager;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Calculates result collations for races')
            ->addArgument('raceIds', InputArgument::IS_ARRAY, 'Race IDs', [])
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $raceIds = $input->getArgument('raceIds');
        array_walk($raceIds, 'intval');

        $overwrite = (bool)$input->getOption('force');

        $raceRepository = $this->em->getRepository(Race::class);
        $racesCount = $raceRepository->getIterationCount($raceIds);
        $iterableResult = $raceRepository->getIterationFindQuery($raceIds)->iterate();

        $i = 1;
        $n = 0;

        while (($res = $iterableResult->next()) !== false) {
            /** @var Race $race */
            $race = $res[0];

            if (!is_null($race->getResultRatioCalculatedAt())) {
                if ($overwrite) {
                    $io->note(sprintf('Overwriting collations for Race #%d', $race->getId()));
                } else {
                    $io->warning(sprintf('Collations for Race #%d was already calculated. Skipping', $race->getId()));

                    continue;
                }
            }

            $this->collationManager->removeCollationsForRace($race);
            $ratiosGenerator = $this->collationManager->calculateCollationsForRace($race);

            foreach ($ratiosGenerator as $j => $ratio) {
                $this->em->persist($ratio);
                if (($j % 500) === 0) {
                    $this->em->flush();
                    $this->em->clear(RaceResultCollation::class);
                }
            }

            $race->setResultRatioCalculatedAt(new \DateTime());
            $this->em->flush();
            $this->em->clear();

            $n += $j;

            $io->note(sprintf('Races: %d #%d (%d) | Ratios: %d (%d) => memory=%d MB', $i++, $race->getId(), $racesCount, $j, $n, (memory_get_usage(1) / 1024 / 1024)));
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}
