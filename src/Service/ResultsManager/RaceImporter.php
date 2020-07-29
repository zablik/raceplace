<?php

namespace App\Service\ResultsManager;

use App\Entity\Profile;
use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use App\Repository\RaceRepository;
use App\Service\ResultPageParsers\OBelarus\DTO\Event as EventDTO;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\ResultPageParsers\OBelarus\Kupalle20200705DataProvider;
use Doctrine\ORM\EntityManagerInterface;

class RaceImporter
{
    private Kupalle20200705DataProvider $dataProvider;
    private EntityManagerInterface $em;
    private ProfileRepository $profileRepository;
    private EventRepository $eventRepository;
    private RaceRepository $raceRepository;

    /** @var EventDTO[]  */
    private array $eventDTOs;

    public function __construct(
        Kupalle20200705DataProvider $dataProvider,
        EntityManagerInterface $em,
        ProfileRepository $profileRepository,
        EventRepository $eventRepository,
        RaceRepository $raceRepository
    ) {
        $this->dataProvider = $dataProvider;
        $this->em = $em;
        $this->profileRepository = $profileRepository;
        $this->eventRepository = $eventRepository;
        $this->raceRepository = $raceRepository;

        $this->eventDTOs = [];
    }

    public function import()
    {
        $this->importProfiles();
        $this->importEvents();

        foreach ($this->loadEvents() as $eventDTO) {
            $event = $this->getEventByDTO($eventDTO);

            foreach ($eventDTO->races as $resultsTable) {
                $race = ParsedDataConverter::convertRace($resultsTable);
                $raceWithResults = $this->raceRepository->findWithResults(
                    $event,
                    $race->getType(),
                    $race->getDistance(),
                    ParsedDataConverter::groupMap()[$resultsTable->group]
                );

                if (!$raceWithResults) {
                    $raceWithResults = $race;
                }

                $this->updateProfileResults($raceWithResults, $resultsTable);

                $raceWithResults->setEvent($event);

                $this->em->persist($raceWithResults);
                $this->em->flush();
            }
        }
    }

    private function compareRaceResults(ProfileResult $profileResult, ProfileResult $data)
    {
        return $data->getNumberPlate() === $profileResult->getNumberPlate()
            && $data->getProfile() === $profileResult->getProfile()
            && $data->getPlace() === $profileResult->getPlace()
            && $data->getTime() == $profileResult->getTime();
    }

    private function updateProfileResults(Race $raceWithResults, ResultsTable $resultsTable)
    {
        /** @var ProfileResult[] $profileResults */
        $profileResults = $raceWithResults->getProfileResults()->getValues();

        // Data from source (i.e. parsed HTML page)
        $profileResultsData = array_map(
            function ($resultsTableRow) use ($resultsTable, $raceWithResults) {
                $profileResult = ParsedDataConverter::convertProfileResult($resultsTableRow);
                $profile = $this->getProfileByDTO($resultsTableRow, $resultsTable->group);
                $profileResult->setProfile($profile);
                $profileResult->setRace($raceWithResults);

                return $profileResult;
            },
            $resultsTable->results
        );

        $resultsToDelete = array_filter($profileResults, function ($profileResult) use ($profileResultsData) {
            foreach ($profileResultsData as $data) {
                if ($this->compareRaceResults($profileResult, $data)) {
                    return false;
                }
            }

            return true;
        });

        $resultsToAdd = array_filter($profileResultsData, function ($data) use ($profileResults) {
            foreach ($profileResults as $profileResult) {
                if ($this->compareRaceResults($profileResult, $data)) {
                    return false;
                }
            }

            return true;
        });

        foreach ($resultsToDelete as $resultToDelete) {
            $raceWithResults->getProfileResults()->removeElement($resultToDelete);
        }

        foreach ($resultsToAdd as $resultToAdd) {
            $raceWithResults->getProfileResults()->add($resultToAdd);
        }

        return $raceWithResults;
    }

    public function importProfiles()
    {
        /** @var Profile[] $profiles */
        $profiles = [];
        $imported = [];
        foreach ($this->loadEvents() as $event) {
            $profiles = array_merge($profiles, $this->getProfiles($event));
        }

        $profiles = array_unique($profiles, SORT_REGULAR);

        foreach ($profiles as $profile) {
            if (!$profile->getId()) {
                $this->em->persist($profile);
                $imported[] = $profile;
            }
        }

        if ($imported) {
            $this->em->flush();
        }

        return $imported;
    }

    public function importEvents()
    {
        $imported = [];

        foreach ($this->loadEvents() as $eventDTO) {
            $event = $this->getEventByDTO($eventDTO);

            if (!$event->getId()) {
                $this->em->persist($event);
                $imported[] = $event;
            }
        }

        if ($imported) {
            $this->em->flush();
        }

        return $imported;
    }

    private function loadEvents()
    {
        if (!$this->eventDTOs) {
            $this->eventDTOs = $this->dataProvider->getEventResults();
        }

        return $this->eventDTOs;
    }

    /**
     * @param EventDTO $event
     * @return Profile[]
     * @throws \Exception
     */
    private function getProfiles(EventDTO $event)
    {
        $profiles = [];
         foreach ($event->races as $resultsTable) {
            foreach ($resultsTable->results as $resultsTableRow) {
                $profiles[] = $this->getProfileByDTO($resultsTableRow, $resultsTable->group);
            }
        }

        return $profiles;
    }

    private function getEventByDTO(EventDTO $eventDTO)
    {
        $event = ParsedDataConverter::convertEvent($eventDTO);

        return $this->eventRepository->findOneBy([
            'name' => $event->getName(),
            'date' => $event->getDate(),
        ]) ?: $event;
    }

    private function getProfileByDTO(ResultsTableRow $resultsTableRow, string $group)
    {
        $profile = ParsedDataConverter::convertProfile($resultsTableRow, $group);

        return $this->profileRepository->findOneBy([
            'name' => $profile->getName(),
            'birthday' => $profile->getBirthday(),
        ]) ?: $profile;
    }
}
