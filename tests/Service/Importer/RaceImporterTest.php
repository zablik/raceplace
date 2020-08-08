<?php

namespace App\Tests\Service\Importer;

use App\DataFixtures\ImportFixtureDump;
use App\Repository\EventRepository;
use App\Service\Importer\DataProvider\Race\RaceDataProviderHub;
use App\Service\Importer\DataProvider\Race\YamlRaceDataProvider;
use App\Service\Importer\RaceImporter;
use App\Service\Utils\TestUtils;
use App\Tests\Traits\DbTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class RaceImporterTest
 * @package App\Tests\Service\Importer
 *
 * @covers \App\Service\Importer\RaceImporter
 */
class RaceImporterTest extends KernelTestCase
{
    use DbTrait;

    /**
     * @dataProvider importProvider
     *
     * @param $eventSlug
     * @param $source
     * @param $dump
     */
    public function testImport($eventSlug, $source, $dump)
    {
        $yamlRaceDataProvider = self::$container->get(YamlRaceDataProvider::class);
        $raceImporter = self::$container->get(RaceImporter::class);
        $eventRepository = self::$container->get(EventRepository::class);
        $serializer = self::$container->get(SerializerInterface::class);
        $kernelProjectDir = self::$container->getParameter('kernel.project_dir');

        $sourceDir = TestUtils::getProtectedProperty($yamlRaceDataProvider, 'sourceDir');
        $sourceDir = str_replace($kernelProjectDir, $kernelProjectDir . '/tests', $sourceDir);
        TestUtils::setProtectedProperty($yamlRaceDataProvider, 'sourceDir', $sourceDir);

        $raceImporter->import($eventSlug, $source);

        $event = $eventRepository->findWithRaces($eventSlug);
        $result = $serializer->serialize($event, 'yaml');

        $this->assertEquals($dump, $result);
    }

    public function importProvider()
    {
        return [
            [
                'zhuk-trail-kupalle-2020',
                RaceDataProviderHub::YAML,
                ImportFixtureDump::RACE_1,
            ],
            [
                'xcm-kupalle-2020',
                RaceDataProviderHub::YAML,
                ImportFixtureDump::RACE_2,
            ]
        ];
    }

    public static function setUpBeforeClass(): void
    {
        self::dropSchema();
        self::createSchema();

        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        self::bootKernel();

        parent::setUp();
    }
}