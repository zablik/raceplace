<?php

namespace App\Service\Utils;

use App\Entity\ProfileCheckpoint;
use App\Service\Importer\Exception\MotionCalculationExcepton;
use \DateTime;

class MotionUtils
{
    /**
     * $pace minutes on 1 km
     */
    public static function paceToSpeed(DateTime $pace)
    {
        $minutes = TimeUtils::getNumberOfMinutes($pace);
        if ((int)$minutes === 0) {
            return null;
        }

        return 60 / TimeUtils::getNumberOfMinutes($pace);
    }

    /**
     * @param float $speed km per 1 hour
     * @return Datetime Pace: minutes on 1 km
     */
    public static function speedToPace(float $speed)
    {
        if (NumberUtils::floatEq($speed, 0)) {
            return null;
        }

        // in minutes
        $pace = 60 / $speed;

        return (new Datetime('1970-01-01 00:00:00'))->modify(sprintf('+%d seconds', $pace * 60));
    }

    public static function recalculateProfileCheckpoint(ProfileCheckpoint $profileCheckpoint)
    {
        if (!$profileCheckpoint->getCheckpoint()) {
            throw new MotionCalculationExcepton('Profile checkpoint has no link to Checkpoint');
        }

        if (!$profileCheckpoint->getTime()) {
            throw new MotionCalculationExcepton('Profile checkpoint has time value');
        }

        $checkpoint = $profileCheckpoint->getCheckpoint();
        $raceCheckpoints = $checkpoint->getRace()->getCheckpoints();
        $key = $raceCheckpoints->indexOf($checkpoint);
        $distanceOfPreviousCheckpoint = 0;
        if ($key > 0) {
            $distanceOfPreviousCheckpoint = $raceCheckpoints->get($key - 1)->getDistance();
        }
        $distance = $checkpoint->getDistance() - $distanceOfPreviousCheckpoint;

        $speed = $distance / TimeUtils::getNumberOfMinutes($profileCheckpoint->getTime()) * 60;
        $pace = self::speedToPace($speed);

        $profileCheckpoint->setSpeed($speed);
        $profileCheckpoint->setPace($pace);

        return $profileCheckpoint;
    }
}
