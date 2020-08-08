<?php

namespace App\Tests\Service\Importer;

use App\DataFixtures\ImportFixtureDump;
use App\Repository\ProfileResultRepository;
use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\Importer\RaceResultsImporter;
use App\Service\Utils\TestUtils;
use App\Service\WebDownloader;
use App\Tests\Traits\DbTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class RaceResultsImporterTest
 * @package App\Tests\Service\Importer
 *
 * @covers \App\Service\Importer\RaceResultsImporter
 */
class RaceResultsImporterTest extends KernelTestCase
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
        $webDownloader = self::$container->get(WebDownloader::class);
        $raceResultsImporter = self::$container->get(RaceResultsImporter::class);
        $profileResultRepository = self::$container->get(ProfileResultRepository::class);
        $serializer = self::$container->get(SerializerInterface::class);
        $kernelProjectDir = self::$container->getParameter('kernel.project_dir');

        $resultsDirPath = TestUtils::getProtectedProperty($webDownloader, 'resultsDirPath');
        $resultsDirPath = str_replace($kernelProjectDir, $kernelProjectDir . '/tests', $resultsDirPath);
        TestUtils::setProtectedProperty($webDownloader, 'resultsDirPath', $resultsDirPath);

        $raceResultsImporter->import($eventSlug, $source);

        $results = $profileResultRepository->findAllArray();
        $result = $serializer->serialize($results, 'yaml');

        $this->assertEquals($dump, $result);
    }

    public function importProvider()
    {
        return [
            [
                'zhuk-trail-kupalle-2020',
                ProfileDataProviderHub::OBELARUS,
                ImportFixtureDump::RESULTS_1,
            ],
        ];
    }

    public function setUp(): void
    {
        self::dropSchema();
        self::createSchema();

        self::bootKernel();

        $this->loadFixtures(['race_results']);

        parent::setUp();
    }
}