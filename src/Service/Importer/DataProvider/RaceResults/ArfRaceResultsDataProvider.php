<?php

namespace App\Service\Importer\DataProvider\RaceResults;

use App\Entity\Race;
use App\Repository\EventRepository;
use App\Service\Importer\DataProvider\WebDataProvider;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\ResultPageParsers\Arf\WebResultsTableParser;
use App\Service\WebDownloader;
use Doctrine\ORM\NonUniqueResultException;

class ArfRaceResultsDataProvider extends WebDataProvider implements RaceResultsDataProviderInterface
{
    private EventRepository $eventRepository;

    public function __construct(
        WebDownloader $webDownloader,
        WebResultsTableParser $resultsTableParser,
        EventRepository $eventRepository
    ) {
        $this->webDownloader = $webDownloader;
        $this->parser = $resultsTableParser;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Race $race
     * @return ResultsTableRow[]
     * @throws NonUniqueResultException
     */
    public function getRaceResultsData(Race $race): array
    {
        $tables = $this->getResults($race->getResultsSource());

        $results = [];
        foreach ($tables as $raceTable) {
            foreach ($raceTable->results as $resultsTableRow) {
                $results[] = $resultsTableRow;
            }
        }

        return $results;
    }
}
