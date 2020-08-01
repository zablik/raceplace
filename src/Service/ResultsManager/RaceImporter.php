<?php

namespace App\Service\ResultsManager;

use App\Entity\Checkpoint;
use App\Entity\Profile;
use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use App\Repository\RaceRepository;
use App\Service\ResultPageParsers\OBelarus\DTO\Checkpoint as CheckpointDTO;
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
//        $this->importProfiles();
//        $this->importEvents();
        //$this->importRaceWithResults();

        //$this->importRaceCheckpoints();

        $this->importProfileCheckpoints();

    }

    private function importProfileCheckpoints()
    {
        $events = array_map(
            fn(EventDTO $eventDTO) => $this->getEventByDTO($eventDTO),
            $this->loadEvents()
        );

        $profileCheckpoints = $this->dataProvider->getProfileCheckpoints();

        foreach ($profileCheckpoints as $profileCheckpoint) {

        }




    }

    private function importRaceCheckpoints()
    {
        $eventDTOs = $this->dataProvider->getEventRaceCheckpoints();

        foreach ($eventDTOs as $eventDTO) {
            $event = $this->getEventByDTO($eventDTO);
            /** @var CheckpointDTO[] $checkpointDTOs */
            foreach ($eventDTO->checkpoints as $raceTypeName => $checkpointDTOs) {
                $raceType = ParsedDataConverter::getRaceTypeByTitle($raceTypeName);
                /** @var Race[] $races */
                $races = $this->raceRepository->findWithCheckpoints($event, $raceType);

                foreach ($races as $race) {
                    /** @var Checkpoint[] $checkpoints */
                    $checkpoints = [];
                    foreach ($checkpointDTOs as $checkpointDTO) {
                        if ($checkpointDTO->distance <= $race->getDistance()) {
                            $checkpoints[] = (new Checkpoint())
                                ->setRace($race)
                                ->setMark($checkpointDTO->mark)
                                ->setDistance($checkpointDTO->distance);
                        }
                    }

                    $toDelete = array_udiff(
                        $race->getCheckpoints()->getValues(),
                        $checkpoints,
                        fn (Checkpoint $stored, Checkpoint $provided) => $this->compareCheckpoints($stored, $provided)
                    );

                    $toAdd = array_udiff(
                        $checkpoints,
                        $race->getCheckpoints()->getValues(),
                        fn (Checkpoint $stored, Checkpoint $provided) => $this->compareCheckpoints($stored, $provided)
                    );

                    foreach ($toDelete as $toDeleteItem) {
                        $race->getCheckpoints()->removeElement($toDeleteItem);
                    }

                    foreach ($toAdd as $toAddItem) {
                        $race->getCheckpoints()->add($toAddItem);
                    }

                    $this->em->persist($race);
                    $this->em->flush();
                }
            }
        }
    }

    private function importRaceWithResults()
    {
        foreach ($this->loadEvents() as $eventDTO) {
            $event = $this->getEventByDTO($eventDTO);

            foreach ($eventDTO->races as $resultsTable) {
                $race = ParsedDataConverter::convertRace($resultsTable);
                $raceWithResults = $this->raceRepository->findWithResults(
                    $event,
                    $race->getType(),
                    $race->getDistance()
                );

                if (!$raceWithResults) {
                    $raceWithResults = $race;
                }

                $group = ParsedDataConverter::groupMap()[$resultsTable->group];
                $this->updateProfileResults($raceWithResults, $resultsTable, $group);

                $raceWithResults->setEvent($event);

                $this->em->persist($raceWithResults);
                $this->em->flush();
            }
        }
    }

    private function compareCheckpoints(Checkpoint $stored, Checkpoint $provided)
    {
        return $stored->getMark() === $provided->getMark()
            && $stored->getDistance() === $provided->getDistance();
    }

    private function compareRaceResults(ProfileResult $profileResult, ProfileResult $data)
    {
        return $data->getNumberPlate() === $profileResult->getNumberPlate()
            && $data->getProfile() === $profileResult->getProfile()
            && $data->getPlace() === $profileResult->getPlace()
            && $data->getTime() == $profileResult->getTime();
    }

    private function updateProfileResults(Race $raceWithResults, ResultsTable $resultsTable, string $group)
    {
        /** @var ProfileResult[] $profileResults */
        $profileResults = $raceWithResults
            ->getProfileResults()
            ->filter(fn(ProfileResult $profileResult) => $profileResult->getProfile()->getGroup() === $group)
            ->getValues();

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
