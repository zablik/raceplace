<?php

namespace App\Service\Importer;

use App\Entity\Profile;
use App\Repository\ProfileRepository;
use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\ResultPageParsers\OBelarus\DTO\Profile as ProfileDto;
use App\Service\ResultPageParsers\OBelarus\WebResultsTableParser;
use App\Service\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;

class ProfileImporter
{
    protected ProfileDataProviderHub $providerHub;
    protected EntityManagerInterface $em;
    protected ProfileRepository $profileRepository;

    public function __construct(
        ProfileDataProviderHub $providerHub,
        EntityManagerInterface $em,
        ProfileRepository $profileRepository
    ) {
        $this->providerHub = $providerHub;
        $this->em = $em;
        $this->profileRepository = $profileRepository;
    }

    public function import(string $eventSlug, string $source)
    {
        $profileDtos = $this->providerHub->getProvider($source)->getProfilesData($eventSlug);

        $this->em->beginTransaction();

        try {
            foreach ($profileDtos as $profileDto) {
                $profile = $this->convertProfileDtoToProfile($profileDto);
                if (!$this->findProfile($profile)) {
                    $this->em->persist($profile);
                }
            }

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            return false;
        }

        $this->em->flush();

        return true;
    }

    private static function groupMap()
    {
        return [
            WebResultsTableParser::GROUP__FEMALE => Profile::GROUP__FEMALE,
            WebResultsTableParser::GROUP__MALE => Profile::GROUP__MALE,
        ];
    }

    private function convertProfileDtoToProfile(ProfileDto $profileDto)
    {
        $profile = (new Profile())
            ->setName($profileDto->name)
            ->setGroup(self::groupMap()[$profileDto->group]);

        if ((int)$profileDto->yearBorn > 1900) {
            $profile->setBirthday(TimeUtils::strYearToDatetime($profileDto->yearBorn));
        }

        return $profile;
    }

    private function findProfile(Profile $profile)
    {
        return $this->profileRepository->findOneBy([
            'name' => $profile->getName(),
            'birthday' => $profile->getBirthday(),
        ]);
    }
}
