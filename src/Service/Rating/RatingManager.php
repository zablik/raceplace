<?php

namespace App\Service\Rating;

use App\Doctrine\Type\RaceResultCollationsType;
use App\Entity\Race;
use App\Repository\RaceResultCollationRepository;

class RatingManager
{
    const NORMALIZE_PERCENT = 7;

    private RaceResultCollationManager $collationManager;
    private $resultCollationRepository;

    public function __construct(
        RaceResultCollationManager $collationManager,
        RaceResultCollationRepository $resultCollationRepository
    )
    {
        $this->collationManager = $collationManager;
        $this->resultCollationRepository = $resultCollationRepository;
    }

    /**
     * @param Race[] $races
     */
    public function generateRating(array $races)
    {
        $matrix = new Matrix();

        $normaliseProfileIds = $this->getNormalizeProfileIds($races);
        $matrix->setNormalizeIds($normaliseProfileIds);
        $allCollations = $this->resultCollationRepository->findArrayForRacesAndProfiles($races, $normaliseProfileIds);

        foreach ($allCollations as $collationRow) {
            $id = $collationRow['profile_id'];
            $raceId = $collationRow['race_id'];

            $collations = json_decode($collationRow['collations'], true);
            foreach ($collations as $collation) {
                $matrix->addCollation(
                    $collation[RaceResultCollationsType::FIELD_PROFILE],
                    $id,
                    $raceId,
                    $collation[RaceResultCollationsType::FIELD_RATIO],
                );
            }
        }

        $matrix->completeMissing();

        return $matrix->getRating();
    }

    /**
     * @param Race[] $races
     */
    private function getNormalizeProfileIds(array $races)
    {
        $profileIds = [];
        $count = 0;
        $frequentProfiles = $this->resultCollationRepository->getProfileFrequencies($races);
        array_walk_recursive($frequentProfiles, fn($item) => intval($item));

        if (empty($frequentProfiles[0]) || $frequentProfiles[0]['total'] < 2) {
            throw new RatingCalculationException('No normalize profiles found');
        }

        $total = array_reduce($frequentProfiles, fn($total, $item) => $total + $item['total']);

        $percentLimit = self::NORMALIZE_PERCENT;
        foreach ($frequentProfiles as $frequentProfile) {
            if (($count / $total) * 100 > $percentLimit) {
                if ($this->resultCollationRepository->getMissingRaceIdsForNormalizeProfiles($races, $profileIds)) {
                    $percentLimit++;
                    continue;
                }

                break;
            }

            $count += $frequentProfile['total'];
            $profileIds[] = (int)$frequentProfile['profile_id'];
        }

        sort($profileIds);

        return $profileIds;
    }
}
