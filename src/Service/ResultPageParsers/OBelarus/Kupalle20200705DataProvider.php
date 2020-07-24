<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\WebDownloader;

class Kupalle20200705DataProvider
{
    const RESULTS_LINK = 'http://www.obelarus.net/results/2020/200705r_zhuk.htm';

    const RACE_TYPE_TITLE__TRAIL = 'Трейл';
    const RACE_TYPE_TITLE__NIGHT_TRAIL = 'Ночной трейл';

    const RACE_TYPE_TITLE__BIKE_MARATHON = 'Веломарафон';
    const RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON = 'Ночной веломарафон';

    const RACE_TYPE_TITLE__MARATHON = 'Марафон';
    const RACE_TYPE_TITLE__NIGHT_MARATHON = 'Ночной марафон';

    const GENDER__MALE = 'Мужчины';
    const GENDER__FEMALE = 'Женщины';

    const MARATHON_DISTANCE = 44;

    private WebDownloader $webDownloader;
    private ResultsTableParser $resultsTableParser;

    public function __construct(WebDownloader $webDownloader, ResultsTableParser $resultsTableParser)
    {
        $this->webDownloader = $webDownloader;
        $this->resultsTableParser = $resultsTableParser;
    }

    public function getResults()
    {
        $html = $this->webDownloader->getHtml(self::RESULTS_LINK);
        $data = $this->resultsTableParser->parseResultsPage($html);

        return $this->prepareResults($data);
    }

    protected function prepareResults(array $resultsData): array
    {
        $resultsTables = [];
        foreach ($resultsData as $resultPart) {
            $results = new ResultsTable();
            $results->type = $resultPart['race'][ResultsTableParser::RACE_TYPE];
            $results->distance = in_array($results->type, [
                self::RACE_TYPE_TITLE__TRAIL,
                self::RACE_TYPE_TITLE__NIGHT_TRAIL,
            ]) ? $resultPart['race'][ResultsTableParser::RACE_DISTANCE] : self::MARATHON_DISTANCE;
            $results->group = $resultPart['race'][ResultsTableParser::RACE_GROUP];

            foreach ($resultPart['results'] as $resultRow) {
                $result = new ResultsTableRow();
                $result->name = $resultRow[ResultsTableParser::COL_NAME];
                $result->regionClub = $resultRow[ResultsTableParser::COL_REGION];
                $result->yearBorn = (int)$resultRow[ResultsTableParser::COL_BORN] ?? null;
                $result->numberPlate = $resultRow[ResultsTableParser::COL_NUMBER_PLATE];
                $result->distance = self::formatDistance($resultRow[ResultsTableParser::COL_DISTANCE]);
                $result->place = (int)$resultRow[ResultsTableParser::COL_PLACE] ?: null;
                $result->note = $resultRow[ResultsTableParser::COL_NOTE];

                $time = $resultRow[ResultsTableParser::COL_TIME];
                $note = $resultRow[ResultsTableParser::COL_NOTE];
                if (preg_match('/DSQ/i', $time)) {
                    $result->note = $time;
                    $result->disqualification = true;
                    if (preg_match('/(\d?\d:\d\d:\d\d)/', $note, $match)) {
                        $time = $match[1];
                    }
                }

                $result->time = self::formatTime($time);

                $results->results[] = $result;
            }

            $resultsTables[] = $results;
        }

        return $resultsTables;
    }

    #region value formatters

    public static function formatDistance(string $value)
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/i', '', $value);

        return floatval($value);
    }

    public static function formatTime(string $time): ?int
    {
        if (!preg_match('/(\d?\d:\d\d:\d\d)/i', $time)) {
            return null;
        }

        return strtotime(sprintf('1970-01-01 %s UTC', $time));
    }

    #endregion
}
