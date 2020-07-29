<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\Exception\ParseResultsException;

class Kupalle20200705ResultsTableParser extends ResultsTableParser
{
    #region Race title parsers
    protected function parseRaceParams(string $raceTitle): ?array
    {
        if ($params = $this->parseTrailParams($raceTitle)) {
            return $params;
        }

        if ($params = $this->parseMarathonParams($raceTitle)) {
            return $params;
        }

        throw new ParseResultsException('Unable to parse race params from title "%s"', $raceTitle);
    }

    private function parseTrailParams(string $raceTitle): ?array
    {
        $regex = sprintf(
            '/(%s|%s)\s(\d{1,3})\sкм\s\-\s(%s|%s)/',
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__TRAIL,
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__NIGHT_TRAIL,
            Kupalle20200705DataProvider::GROUP__MALE,
            Kupalle20200705DataProvider::GROUP__FEMALE
        );

        $params = null;
        if (preg_match($regex, $raceTitle, $matches)) {
            $params = [
                ResultsTableParser::RACE_TYPE => $matches[1],
                ResultsTableParser::RACE_DISTANCE => $matches[2],
                ResultsTableParser::RACE_GROUP => $matches[3],
            ];
        }

        return $params;
    }

    private function parseMarathonParams(string $raceTitle): ?array
    {
        $regex = sprintf(
            '/(%s|%s|%s|%s)\s\-\s(%s|%s)/',
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__MARATHON,
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__NIGHT_MARATHON,
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__BIKE_MARATHON,
            Kupalle20200705DataProvider::RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON,
            Kupalle20200705DataProvider::GROUP__MALE,
            Kupalle20200705DataProvider::GROUP__FEMALE
        );

        $params = null;
        if (preg_match($regex, $raceTitle, $matches)) {
            $params = [
                ResultsTableParser::RACE_TYPE => $matches[1],
                ResultsTableParser::RACE_GROUP => $matches[2],
            ];
        }

        return $params;
    }
    #endregion
}