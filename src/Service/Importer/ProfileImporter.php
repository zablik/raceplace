<?php

namespace App\Service\Importer;

use App\Entity\Event;
use App\Entity\Profile;
use App\Repository\ProfileRepository;
use App\Service\Importer\DataProvider\Profile\ProfileDataProviderHub;
use App\Service\ResultPageParsers\DTO\Profile as ProfileDto;
use App\Service\ResultPageParsers\OBelarus\TableConfig;
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
        $event = $this->em->getRepository(Event::class)->findOneBy(['slug' => $eventSlug]);

        $this->em->beginTransaction();

        try {
            foreach ($profileDtos as $profileDto) {
                $profile = $this->convertProfileDtoToProfile($profileDto);
                if (!$existed = $this->findProfile($profile)) {
                    $this->em->persist($profile);
                } else {
                    $this->updateProfile($existed, $profile);
                }
            }

            if ($profileDtos) {
                $event->setProfilesImportedAt(new \DateTime());
            }

            $this->em->commit();
            $this->em->flush();
        } catch (\Exception $e) {
            $this->em->rollback();

            return false;
        }

        return true;
    }

    private static function groupMap()
    {
        return [
            TableConfig::GROUP__FEMALE => Profile::GROUP__FEMALE,
            TableConfig::GROUP__MALE => Profile::GROUP__MALE,
        ];
    }

    private function updateProfile(Profile $existed, Profile $new)
    {
        $fields = [
            'arfId',
            'region',
            'club',
            'stravaId',
            'birthday',
        ];

        array_walk($fields, function ($field) use ($existed, $new) {
            $getter = sprintf('get%s', ucfirst($field));
            $setter = sprintf('set%s', ucfirst($field));
            if (!empty($new->{$getter}()) && empty($existed->{$getter}())) {
                $existed->{$setter}($new->{$getter}());
            }
        });
    }

    private function convertProfileDtoToProfile(ProfileDto $profileDto)
    {
        $profile = (new Profile())
            ->setName($profileDto->name)
            ->setGroup(self::groupMap()[$profileDto->group])
            ->setArfId($profileDto->arfId)
            ->setStravaId($profileDto->stravaId)
        ;

        if ((int)$profileDto->yearBorn > 1900) {
            $profile->setBirthday(TimeUtils::strYearToDatetime($profileDto->yearBorn));
        }

        if (!empty($profileDto->regionClub)) {
            $parts = explode(',', $profileDto->regionClub);
            $profile->setRegion(array_shift($parts));
            if (!empty($parts)) {
                $profile->setClub(trim(implode(',', $parts)));
            }
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
