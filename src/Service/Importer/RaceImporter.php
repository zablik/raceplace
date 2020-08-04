<?php

namespace App\Service\Importer;

use App\Entity\Checkpoint;
use App\Entity\Event;
use App\Entity\Race;
use App\Repository\EventRepository;
use App\Service\Importer\DataProvider\Race\RaceDataProviderHub;
use App\Service\Utils\CollectionUtils;
use App\Service\Utils\NumberUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RaceImporter
{
    protected RaceDataProviderHub $providerHub;
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected EventRepository $eventRepository;

    public function __construct(
        RaceDataProviderHub $providerHub,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        EventRepository $eventRepository
    ) {
        $this->providerHub = $providerHub;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->eventRepository = $eventRepository;
    }

    public function import(string $eventSlug, string $source)
    {
        $data = $this->providerHub->getProvider($source)->getEventRacesData($eventSlug);
        /** @var Event $inputEvent */
        $inputEvent = $this->serializer->denormalize($data, Event::class);

        $event = $this->eventRepository->findWithRaces($eventSlug) ?: (new Event())->setSlug($eventSlug);

        $event
            ->setName($inputEvent->getName())
            ->setLink($inputEvent->getLink())
            ->setDate($inputEvent->getDate())
        ;

        $existingRaces = CollectionUtils::importCollection(
            $event,
            $event->getRaces()->getValues(),
            $inputEvent->getRaces()->getValues(),
            'Race',
            fn(Race $existing, Race $input) => self::compareRaces($existing, $input),
            ['slug', 'resultsSource']
        );

        // for races that already were in DB we update nested associations
        foreach ($existingRaces as $existingRace) {
            $inputRace = array_values(array_filter(
                $inputEvent->getRaces()->getValues(),
                fn (Race $inputItem) => self::compareRaces($existingRace, $inputItem)
            ))[0];

            CollectionUtils::importCollection(
                $existingRace,
                $existingRace->getCheckpoints()->getValues(),
                $inputRace->getCheckpoints()->getValues(),
                'Checkpoint',
                fn(Checkpoint $existing, Checkpoint $input) => self::compareCheckpoint($existing, $input),
            );
        }

        $this->em->persist($event);
        $this->em->flush();
    }

    private static function compareRaces(Race $existing, Race $input)
    {
        return NumberUtils::floatEq($existing->getDistance(), $input->getDistance())
            && $existing->getType() === $input->getType();
    }

    private static function compareCheckpoint(Checkpoint $existing, Checkpoint $input)
    {
        return NumberUtils::floatEq($existing->getDistance(), $input->getDistance())
            && $existing->getMark() === $input->getMark();
    }
}
