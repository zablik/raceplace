<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class EventRaceFixtures extends Fixture implements FixtureGroupInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function load(ObjectManager $manager)
    {
        $event = $this->serializer->deserialize(ImportFixtureDump::RACE_1, Event::class, 'yaml');
        $manager->persist($event);

        $manager->flush();

        $this->setReference('Event_1', $event);
    }

     public static function getGroups(): array
     {
         return ['profiles', 'race_results', 'checkpoints'];
     }
}
