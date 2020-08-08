<?php

namespace App\DataFixtures;

use App\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class ProfilesFixtures extends Fixture implements FixtureGroupInterface
{
    private SerializerInterface $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function load(ObjectManager $manager)
    {
        $profiles = $this->serializer->deserialize(ImportFixtureDump::PROFILES_1, Profile::class . '[]', 'yaml');
        foreach ($profiles as $n => $profile) {
            $manager->persist($profile);
            $this->setReference('Profile_' . $n, $profile);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['race_results', 'checkpoints'];
    }
}
