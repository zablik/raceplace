<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\OBelarus\DTO\Event;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\WebDownloader;

class Kupalle20200705DataProvider extends DataProvider
{
    const RESULTS_LINK = 'http://www.obelarus.net/results/2020/200705r_zhuk.htm';

    const EVENT_NAME__ZHUK_TRAIL = 'Забег «Жук-трейл # 17 Купалье»';
    const EVENT_NAME__MARATHON = 'Марафон «Купалье»';
    const EVENT_NAME__BIKE_MARATHON = 'Веломарафон «Купалье»';

    const MARATHON_DISTANCE = 44;

    private WebDownloader $webDownloader;
    private ResultsTableParser $resultsTableParser;

    public function __construct(WebDownloader $webDownloader, ResultsTableParser $resultsTableParser)
    {
        $this->webDownloader = $webDownloader;
        $this->resultsTableParser = $resultsTableParser;
    }

    private static function getRaceTypeConfig()
    {
        return [
            self::EVENT_NAME__ZHUK_TRAIL => [
                self::RACE_TYPE_TITLE__TRAIL,
                self::RACE_TYPE_TITLE__NIGHT_TRAIL,
            ],
            self::EVENT_NAME__MARATHON => [
                self::RACE_TYPE_TITLE__MARATHON,
                self::RACE_TYPE_TITLE__NIGHT_MARATHON,
            ],
            self::EVENT_NAME__BIKE_MARATHON => [
                self::RACE_TYPE_TITLE__BIKE_MARATHON,
                self::RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON,
            ],
        ];
    }

    /**
     * @return Event[]
     */
    public function getEventResults()
    {
        $html = $this->webDownloader->getHtml(self::RESULTS_LINK);
        $data = $this->resultsTableParser->parseResultsPage($html);
        $resultTables = $this->prepareResults($data);

        $trail= new Event();
        $trail->name = self::EVENT_NAME__ZHUK_TRAIL;
        $trail->date = new \DateTime('2020-06-03');
        $trail->link = 'https://www.arf.by/?index=events-future&id=2020-zhuktrail-17-kupalle';

        $marathon = new Event();
        $marathon->name = self::EVENT_NAME__MARATHON;
        $marathon->date = new \DateTime('2020-06-03');
        $marathon->link = 'https://www.arf.by/?index=events-future&id=2020-kupalle';

        $bikeMarathon = new Event();
        $bikeMarathon->name = self::EVENT_NAME__BIKE_MARATHON;
        $bikeMarathon->date = new \DateTime('2020-06-03');
        $bikeMarathon->link = 'https://www.arf.by/?index=events-future&id=2020-kupalle';

        $this->attachRaceResultsToEvent($trail, $resultTables);
        $this->attachRaceResultsToEvent($marathon, $resultTables);
        $this->attachRaceResultsToEvent($bikeMarathon, $resultTables);

        return [$trail, $bikeMarathon, $marathon];
    }

    protected function prepareResults(array $resultsData): array
    {
        $resultsTables = [];
        foreach ($resultsData as $resultPart) {
            $resultsTable = $this->prepareResultsTable($resultPart);

            foreach ($resultPart['results'] as $resultRow) {
                $resultsTable->results[] = $this->prepareResultsTableRow($resultRow);
            }

            $resultsTables[] = $resultsTable;
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

        return strtotime(sprintf('1970-01-01 %s', $time));
    }

    #endregion


    /**
     * @param array $resultRow
     * @return ResultsTableRow
     */
    protected function prepareResultsTableRow(array $resultRow): ResultsTableRow
    {
        $result = new ResultsTableRow();
        $result->name = $resultRow[ResultsTableParser::COL_NAME];
        $result->regionClub = $resultRow[ResultsTableParser::COL_REGION];
        $result->yearBorn = (int)$resultRow[ResultsTableParser::COL_BORN] ?? null;
        $result->numberPlate = $resultRow[ResultsTableParser::COL_NUMBER_PLATE];
        $result->distance = self::formatDistance($resultRow[ResultsTableParser::COL_DISTANCE]);
        $result->place = (int)$resultRow[ResultsTableParser::COL_PLACE] ?: null;
        $result->note = $resultRow[ResultsTableParser::COL_NOTE];

        $time = $resultRow[ResultsTableParser::COL_TIME];
        $note = $resultRow[ResultsTableParser::COL_NOTE];;
        if (preg_match('/DSQ/i', $time)) {
            $result->note = $time;
            $result->disqualification = true;
            if (preg_match('/(\d?\d:\d\d:\d\d)/', $note, $match)) {
                $time = $match[1];
            }
        }

        $result->time = self::formatTime($time);

        return $result;
    }

    /**
     * @param $resultPart
     * @return ResultsTable
     */
    protected function prepareResultsTable($resultPart): ResultsTable
    {
        $results = new ResultsTable();
        $results->type = $resultPart['race'][ResultsTableParser::RACE_TYPE];
        $results->distance = in_array($results->type, [
            self::RACE_TYPE_TITLE__TRAIL,
            self::RACE_TYPE_TITLE__NIGHT_TRAIL,
        ]) ? $resultPart['race'][ResultsTableParser::RACE_DISTANCE] : self::MARATHON_DISTANCE;
        $results->group = $resultPart['race'][ResultsTableParser::RACE_GROUP];

        return $results;
    }

    /**
     * @param Event $event
     * @param ResultsTable[] $resultsTables
     * @return ResultsTable[]|array
     */
    protected function attachRaceResultsToEvent(Event $event, array $resultsTables)
    {
        $event->races = array_filter(
            $resultsTables,
            fn($resultsTable) => in_array($resultsTable->type, self::getRaceTypeConfig()[$event->name])
        );

        return $event->races;
    }
}