<?php

namespace App\Service\Utils;

use \DateTime;
use \DateTimeInterface;

class TimeUtils
{
    /**
     * @param string $year
     * @return DateTime
     * @throws \Exception
     */
    public static function strYearToDatetime(string $year)
    {
        return new DateTime($year . '-01-01 00:00:00');
    }

    /**
     * @param string $time '01:23:57'
     * @return DateTime|null
     * @throws \Exception
     */
    public static function strTimeToDatetime(string $time)
    {
        if (!preg_match('/(?:(\d?\d):)?(\d?\d?\d):(\d\d)/', $time, $matches)) {
            return null;
        }

        $hours = (int)$matches[1];
        $min = (int)$matches[2];
        $sec = (int)$matches[3];

        if ($min >= 60) {
            $hours += intdiv($min, 60);
            $min = $min % 60;
        }

        return new DateTime(sprintf('1970-01-01 %d:%d:%d', $hours, $min, $sec));
    }

    public static function getNumberOfMinutes(DateTimeInterface $time): ?float
    {
        return self::numberOfSeconds($time) / 60;
    }

    public static function numberOfSeconds(DateTimeInterface $time): ?float
    {
        return $time->getTimestamp() - (new DateTime('1970-01-01 00:00:00'))->getTimestamp();
    }
}
