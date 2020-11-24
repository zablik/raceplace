<?php

namespace App\Service\Importer\DataProvider\Profile;

use App\Repository\EventRepository;
use App\Service\Importer\DataProvider\WebDataProvider;
use App\Service\Importer\Exception\DataProviderExcepton;
use App\Service\ResultPageParsers\DTO\Profile;
use App\Service\ResultPageParsers\DTO\ResultsTable;
use App\Service\ResultPageParsers\Arf\WebResultsTableParser;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\WebDownloader;
use Doctrine\ORM\NonUniqueResultException;

class ArfProfileDataProvider extends WebDataProvider implements ProfileDataProviderInterface
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
     * @param string $eventSlug
     * @return Profile[]
     * @throws NonUniqueResultException
     */
    public function getProfilesData(string $eventSlug): array
    {
        $event = $this->eventRepository->findWithRaces($eventSlug);

        if (!$event) {
            throw new DataProviderExcepton(sprintf('No event found for slug "%s"', $eventSlug));
        }

        $tables = [];
        foreach ($event->getRaces() as $race) {
            $resultTables = $this->getResults($race->getResultsSource());

            $tables = array_merge($tables, $resultTables);
        }

        return $this->getGetProfilesFromResultTables($tables);
    }

    /**
     * @param ResultsTable[] $tables
     * @return Profile[]
     */
    private function getGetProfilesFromResultTables(array $tables): array
    {
        $profiles = [];
        foreach ($tables as $table) {
            foreach ($table->results as $tableRow) {
                $profiles[] = $this->getGetProfileFromResultTables($tableRow, $table);
            }
        }

        return array_unique($profiles, SORT_REGULAR);
    }

    private function getGetProfileFromResultTables(ResultsTableRow $tableRow, ResultsTable $table): Profile
    {
        $profile = new Profile();
        $profile->name = $tableRow->name;
        $profile->yearBorn = $tableRow->yearBorn;
        $profile->regionClub = $tableRow->regionClub;
        $profile->group = $table->group;
        $profile->arfId = $tableRow->arfId;

        return $profile;
    }
}
