<?php

namespace App\Service\Importer;

use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Repository\EventRepository;
use App\Repository\ProfileRepository;
use App\Service\Importer\DataProvider\RaceResults\RaceResultsDataProviderHub;
use App\Service\Importer\Exception\DataProviderExcepton;
use App\Service\ResultPageParsers\DTO\ResultsTableRow;
use App\Service\Utils\CollectionUtils;
use App\Service\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RaceResultsImporter
{
    protected RaceResultsDataProviderHub $providerHub;
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected EventRepository $eventRepository;
    protected ProfileRepository $profileRepository;

    public function __construct(
        RaceResultsDataProviderHub $providerHub,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        EventRepository $eventRepository,
        ProfileRepository $profileRepository
    ) {
        $this->providerHub = $providerHub;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->eventRepository = $eventRepository;
        $this->profileRepository = $profileRepository;
    }

    public function importRaceResults(Race $race)
    {
        $results = $this->providerHub->getProvider($race->getResultsSource()->getType())->getRaceResultsData($race);

        $profileResults = [];
        foreach ($results as $result) {
            $profile = $this->findProfile($result);

            if (!$profile) {
                throw new DataProviderExcepton(
                    sprintf('No profile for "%s" in DB! Import profiles first', $result->name)
                );
            }

            $profileResult = $this->convertToProfileResult($result);
            $profileResult
                ->setRace($race)
                ->setProfile($profile);

            $profileResults[] = $profileResult;
        }

        CollectionUtils::importCollection(
            $race,
            $race->getProfileResults()->getValues(),
            $profileResults,
            'ProfileResult',
            fn(ProfileResult $res1, ProfileResult $res2) => $this->compareProfileResults($res1, $res2)
        );

        $this->em->persist($race);
        $this->em->flush();
    }

    public function import(string $eventSlug)
    {
        $event = $this->eventRepository->findWithRaces($eventSlug);

        if (!$event) {
            throw new \Exception(sprintf('No event found for slug "%s"', $eventSlug));
        }

        foreach ($event->getRaces() as $race) {
            $this->importRaceResults($race);
        }
    }

    private function compareProfileResults(ProfileResult $res1, ProfileResult $res2)
    {
        return $res1->getNumberPlate() === $res2->getNumberPlate()
            && $res1->getProfile() === $res2->getProfile()
            && $res1->getPlace() === $res2->getPlace()
            && $res1->getTime() == $res2->getTime();
    }

    private function findProfile(ResultsTableRow $resultsTableRow)
    {
        $birthday = null;
        if ((int)$resultsTableRow->yearBorn > 1900) {
            $birthday = TimeUtils::strYearToDatetime($resultsTableRow->yearBorn);
        }

        return $this->profileRepository->findOneBy([
            'name' => $resultsTableRow->name,
            'birthday' => $birthday,
        ]);
    }

    private function convertToProfileResult(ResultsTableRow $resultsTableRow)
    {
        $profileResult = (new ProfileResult())
            ->setNote($resultsTableRow->note)
            ->setPlace((int)$resultsTableRow->place ?: null)
            ->setNumberPlate($resultsTableRow->numberPlate)
            ->setDistance(floatval($resultsTableRow->distance))
        ;

        $time = $resultsTableRow->time;
        $note = $resultsTableRow->note;
        if (preg_match('/(DSQ|DNF)/i', $time)) {
            $profileResult->setNote($time);
            $profileResult->setDisqualification(true);
            if (preg_match('/(\d?\d:\d\d:\d\d)/', $note, $match)) {
                $time = $match[1];
            }
        }

        $profileResult->setTime(TimeUtils::strTimeToDatetime($time));

        return $profileResult;
    }
}
