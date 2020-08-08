<?php

namespace App\Tests\Service\Importer;

use App\DataFixtures\ImportFixtureDump;
use App\Repository\ProfileRepository;
use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\Importer\ProfileImporter;
use App\Service\Utils\TestUtils;
use App\Service\WebDownloader;
use App\Tests\Traits\DbTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class ProfileImporterTest
 * @package App\Tests\Service\Importer
 *
 * @covers \App\Service\Importer\ProfileImporter
 */
class ProfileImporterTest extends KernelTestCase
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
        $profileImporter = self::$container->get(ProfileImporter::class);
        $profileRepository = self::$container->get(ProfileRepository::class);
        $serializer = self::$container->get(SerializerInterface::class);
        $kernelProjectDir = self::$container->getParameter('kernel.project_dir');

        $resultsDirPath = TestUtils::getProtectedProperty($webDownloader, 'resultsDirPath');
        $resultsDirPath = str_replace($kernelProjectDir, $kernelProjectDir . '/tests', $resultsDirPath);
        TestUtils::setProtectedProperty($webDownloader, 'resultsDirPath', $resultsDirPath);

        $profileImporter->import($eventSlug, $source);

        $profiles = $profileRepository->findAll();
        $result = $serializer->serialize($profiles, 'yaml');

        $this->assertEquals($dump, $result);
    }

    public function importProvider()
    {
        return [
            [
                'zhuk-trail-kupalle-2020',
                ProfileDataProviderHub::OBELARUS,
                ImportFixtureDump::PROFILES_1,
            ],
        ];
    }

    public function setUp(): void
    {
        self::dropSchema();
        self::createSchema();

        $this->loadFixtures(['profiles']);

        self::bootKernel();

        parent::setUp();
    }
}