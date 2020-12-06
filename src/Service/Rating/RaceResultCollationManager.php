<?php

namespace App\Service\Rating;

use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Entity\RaceResultCollation;
use App\Entity\ResultCollation;
use App\Entity\ResultRatio;
use App\Repository\RaceRepository;
use App\Repository\RaceResultCollationRepository;
use App\Service\Utils\ArrayUtils;
use App\Service\Utils\NumberUtils;
use Doctrine\ORM\EntityManagerInterface;

class RaceResultCollationManager
{
    private $resultCollationRepository;
    private $raceRepository;
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        RaceResultCollationRepository $resultCollationRepository,
        RaceRepository $raceRepository
    )
    {
        $this->resultCollationRepository = $resultCollationRepository;
        $this->raceRepository = $raceRepository;
        $this->em = $em;
    }

    public function calculateCollationsForRace(Race $race)
    {
        $profileResults = $this->getSuccessfulProfileResults($race);

        foreach ($profileResults as $profileResult) {
            $raceResultCollation = (new RaceResultCollation())
                ->setProfile($profileResult->getProfile())
                ->setRace($race)
            ;
            foreach ($profileResults as $otherProfileResult) {
                if ($profileResult->getId() !== $otherProfileResult->getId()) {
                    $raceResultCollation->addCollation($this->createCollation($otherProfileResult, $profileResult));
                }
            }

            yield $raceResultCollation;
        }
    }

    public function removeCollationsForRace(Race $race)
    {
        $ratios = $this->resultCollationRepository->findByRace($race);
        foreach ($ratios as $ratio) {
            $this->em->remove($ratio);
        }
        $race->setResultRatioCalculatedAt(null);
    }

    /**
     * @param Race $race
     * @return ProfileResult[]
     */
    private function getSuccessfulProfileResults(Race $race): array
    {
        return $race->getProfileResults()->filter(function (ProfileResult $profileResult) use ($race) {
            return !$profileResult->getDisqualification()
                && is_float($profileResult->getDistance())
                && !is_null($profileResult->getTime())
                && $profileResult->getTime()->getTimestamp() > 0
                && NumberUtils::floatEq($profileResult->getDistance(), $race->getDistance());
        })->toArray();
    }

    private static function power(ProfileResult $result)
    {
        return 1 / $result->getTime()->getTimestamp();
    }

    private function createCollation(ProfileResult $profileResult, ProfileResult $normalizedProfileResult)
    {
        $ratio = round(self::power($profileResult) / self::power($normalizedProfileResult), 3);

        return (new ResultCollation())
            ->setProfileId($profileResult->getProfile()->getId())
//            ->setProfile($normalizedProfileResult->getProfile())
            ->setRatio($ratio);
    }
}
