<?php

namespace App\Service\ResultsManager;

use App\Entity\Event;
use App\Entity\Profile;
use App\Entity\ProfileResult;
use App\Entity\Race;
use App\Service\ResultPageParsers\OBelarus\DataProvider;
use App\Service\ResultPageParsers\OBelarus\DTO\Event as EventDTO;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\ResultsManager\Exception\ConversionException;

class ParsedDataConverter
{
    /**
     * @param ResultsTableRow $resultsTableRow
     * @param string $raceGroup
     * @return Profile
     * @throws \Exception
     */
    public static function convertProfile(ResultsTableRow $resultsTableRow, string $raceGroup)
    {
        $profile = (new Profile())
            ->setName($resultsTableRow->name)
            ->setBirthday(new \DateTime($resultsTableRow->yearBorn . '-01-01 00:00:00'))
            ->setGroup(self::groupMap()[$raceGroup]);

        return $profile;
    }

    /**
     * @param ResultsTable $resultsTable
     * @return Race
     */
    public static function convertRace(ResultsTable $resultsTable)
    {
        $race = (new Race())
            ->setType(self::getRaceTypeByTitle($resultsTable->type))
            ->setDistance($resultsTable->distance);

        return $race;
    }

    public static function convertProfileResult(ResultsTableRow $resultsTableRow)
    {
        $profileResult = (new ProfileResult())
            ->setPlace($resultsTableRow->place)
            ->setNumberPlate($resultsTableRow->numberPlate)
            ->setDisqualification($resultsTableRow->disqualification)
            ->setNote($resultsTableRow->note);

        if ($resultsTableRow->time) {
            $profileResult->setTime((new \DateTime)->setTimestamp($resultsTableRow->time));
        }

        return $profileResult;

    }

    /**
     * @param EventDTO $eventDTO
     * @return Event
     */
    public static function convertEvent(EventDTO $eventDTO)
    {
        $event = (new Event())
            ->setName($eventDTO->name)
            ->setDate($eventDTO->date)
            ->setLink($eventDTO->link);

        return $event;
    }

    private static function getRaceTypeByTitle(string $typeTitle)
    {
        foreach (self::raceTypeMap() as $type => $titles) {
            if (in_array($typeTitle, $titles)) {
                return $type;
            }
        }

        throw new ConversionException(sprintf('Unexpected race type title: "%s"', $typeTitle));
    }

    private static function raceTypeMap()
    {
        return [
            Race::TYPE__TRAIL => [
                DataProvider::RACE_TYPE_TITLE__TRAIL,
            ],
            Race::TYPE__NIGHT_TRAIL => [
                DataProvider::RACE_TYPE_TITLE__NIGHT_TRAIL,
            ],
            Race::TYPE__MARATHON => [
                DataProvider::RACE_TYPE_TITLE__MARATHON,
            ],
            Race::TYPE__NIGHT_MARATHON => [
                DataProvider::RACE_TYPE_TITLE__NIGHT_MARATHON,
            ],
            Race::TYPE__XCM => [
                DataProvider::RACE_TYPE_TITLE__BIKE_MARATHON,
            ],
            Race::TYPE__NIGHT_XCM => [
                DataProvider::RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON,
            ],
        ];
    }

    public static function groupMap()
    {
        return [
            DataProvider::GROUP__FEMALE => Profile::GROUP__FEMALE,
            DataProvider::GROUP__MALE => Profile::GROUP__MALE,
        ];
    }
}
