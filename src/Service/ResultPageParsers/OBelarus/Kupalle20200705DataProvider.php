<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Service\ResultPageParsers\Exception\ParseResultsException;
use App\Service\ResultPageParsers\OBelarus\DTO\Checkpoint;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTableRow;
use App\Service\ResultPageParsers\OBelarus\DTO\Event;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\ResultsTableRow;
use App\Service\WebDownloader;
use DateTime;

class Kupalle20200705DataProvider extends DataProvider
{
    const RESULTS_LINK = 'http://www.obelarus.net/results/2020/200705r_zhuk.htm';
    const CHECKPOINTS_LINK = 'http://www.obelarus.net/results/2020/200705s_zhuk.htm';

    const EVENT_NAME__ZHUK_TRAIL = 'Забег «Жук-трейл # 17 Купалье»';
    const EVENT_NAME__MARATHON = 'Марафон «Купалье»';
    const EVENT_NAME__BIKE_MARATHON = 'Веломарафон «Купалье»';

    const MARATHON_DISTANCE = 44;

    private WebDownloader $webDownloader;
    private ResultsTableParser $resultsTableParser;
    private CheckpointsParser $checkpointsParser;

    public function __construct(
        WebDownloader $webDownloader,
        ResultsTableParser $resultsTableParser,
        CheckpointsParser $checkpointsParser
    ) {
        $this->webDownloader = $webDownloader;
        $this->resultsTableParser = $resultsTableParser;
        $this->checkpointsParser = $checkpointsParser;
    }

    #region configs
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
     * @return array[]
     */
    private static function getRaceCheckpointsConfig()
    {
        $trailConf = [
            ['Круг 1', 5],
            ['Круг 2', 10],
            ['Круг 3', 15],
            ['Круг 4', 20],
        ];

        $marathonConf = [
            ['КП 1. Выход дороги из леса. Крупное дерево слева', 3],
            ['КП 2. Перекресток дорог на вершине холма. Руины стальной вышки высотой 6м', 11.6],
            ['КП 3. Примыкание лесной дороги. Дерево на углу справа', 14.4],
            ['КП 4. Пересечение канала и реки. Крупная отдельная ель около канала', 18.2],
            ['КП 5. Начало ливнепровода-акведука. Небольшое v-образное дерево слева.', 28.9],
            ['КП 6. Опора ЛЭП #381', 29.4],
            ['КП 7. Выход дороги из леса. Береза слеваа от дороги', 36.3],
            ['КП 8. Угол леса. Береза', 41],
            ['Финиш', 44],
        ];

        return [
            self::RACE_TYPE_TITLE__TRAIL => $trailConf,
            self::RACE_TYPE_TITLE__NIGHT_TRAIL => $trailConf,
            self::RACE_TYPE_TITLE__MARATHON => $marathonConf,
            self::RACE_TYPE_TITLE__NIGHT_MARATHON => $marathonConf,
            self::RACE_TYPE_TITLE__BIKE_MARATHON => $marathonConf,
            self::RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON => $marathonConf,
        ];
    }

    #endregion

    /**
     * @return Event[]
     */
    public function getEventResults()
    {
        $html = $this->webDownloader->getHtml(self::RESULTS_LINK);
        $data = $this->resultsTableParser->parseResultsPage($html);
        $resultTables = $this->prepareResults($data);

        list($trail, $marathon, $bikeMarathon) = $this->getEvents();

        $this->attachRaceResultsToEvent($trail, $resultTables);
        $this->attachRaceResultsToEvent($marathon, $resultTables);
        $this->attachRaceResultsToEvent($bikeMarathon, $resultTables);

        return [$trail, $marathon, $bikeMarathon];
    }

    /**
     * @return Event[]
     */
    public function getEventRaceCheckpoints()
    {
        $checkpointsConfig = self::getRaceCheckpointsConfig();
        $eventRaceConfig = self::getRaceTypeConfig();
        $events = $this->getEvents();

        foreach ($events as $event) {
            foreach ($eventRaceConfig[$event->name] as $raceType) {
                $event->checkpoints[$raceType] = [];

                foreach ($checkpointsConfig[$raceType] as $config) {
                    $checkpoint = new Checkpoint();
                    $checkpoint->mark = $config[0];
                    $checkpoint->distance = $config[1];
                    // TODO: #DTO Move checkpoints from event DTO to race DTO
                    $event->checkpoints[$raceType][] = $checkpoint;
                }
            }
        }

        return $events;
    }

    public function getProfileCheckpoints()
    {
        $html = $this->webDownloader->getHtml(self::CHECKPOINTS_LINK);
        $data = $this->checkpointsParser->parseCheckpointsPage($html);

        return $this->prepareProfileCheckpoints($data);
    }

    protected function getEvents()
    {
        $trail = new Event();
        $trail->name = self::EVENT_NAME__ZHUK_TRAIL;
        $trail->date = new DateTime('2020-06-03');
        $trail->link = 'https://www.arf.by/?index=events-future&id=2020-zhuktrail-17-kupalle';

        $marathon = new Event();
        $marathon->name = self::EVENT_NAME__MARATHON;
        $marathon->date = new DateTime('2020-06-03');
        $marathon->link = 'https://www.arf.by/?index=events-future&id=2020-kupalle';

        $bikeMarathon = new Event();
        $bikeMarathon->name = self::EVENT_NAME__BIKE_MARATHON;
        $bikeMarathon->date = new DateTime('2020-06-03');
        $bikeMarathon->link = 'https://www.arf.by/?index=events-future&id=2020-kupalle';

        return [$trail, $marathon, $bikeMarathon];
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

    protected function prepareProfileCheckpoints(array $checkpointsData): array
    {
        $checkpoints = [];

        foreach ($checkpointsData as $data) {
            $table = new CheckpointsTable();
            $table->name = $data[CheckpointsParser::PROFILE][CheckpointsParser::NAME];
            $table->numberPlate = $data[CheckpointsParser::PROFILE][CheckpointsParser::NUMBER_PLATE];

            $table->raceCode = $data[CheckpointsParser::PROFILE][CheckpointsParser::RACE_CODE];

            foreach ($data[CheckpointsParser::CHECKPOINTS] as $checkpoint) {
                $tableRow = new CheckpointsTableRow();
                /** @var int $time */
                if ($time = self::strToTimestamp($checkpoint[CheckpointsParser::TIME])) {
                    $tableRow->time = $time;
                }
                /** @var int $totalTime */
                if ($totalTime = self::strToTimestamp($checkpoint[CheckpointsParser::TOTAL_TIME])) {
                    $tableRow->totalTime = $totalTime;
                }
                // TODO: update values!
                $tableRow->distance = self::strToFloat($checkpoint[CheckpointsParser::DISTANCE]);
                $tableRow->pace = self::strToTimestamp($checkpoint[CheckpointsParser::PACE]);

                $table->checkpoints[] = $tableRow;
            }

            $checkpoints[] = $table;
        }

        return $checkpoints;
    }

    /**
     * @param Event[] $events
     * @param string $code
     */
    protected function getEventAndRaceByCode(array $events, string $code): Event
    {
        if (!preg_match('/(Т|Вело|Мара)(\d\d)?:([МЖ])(Н)?/i', $code, $matches)) {
            throw new ParseResultsException(sprintf('Unexpected race code "%s"', $code));
        }

        $eventsMap = [
            'Т' => self::EVENT_NAME__ZHUK_TRAIL,
            'Вело' => self::EVENT_NAME__BIKE_MARATHON,
            'Мара' => self::EVENT_NAME__MARATHON,
        ];

        $raceTypeMap = [
            'T' => self::RACE_TYPE_TITLE__TRAIL,
            'TН' => self::RACE_TYPE_TITLE__NIGHT_TRAIL,
            'Вело' => self::RACE_TYPE_TITLE__BIKE_MARATHON,
            'ВелоН' => self::RACE_TYPE_TITLE__NIGHT_BIKE_MARATHON,
            'Мара' => self::RACE_TYPE_TITLE__MARATHON,
            'МараН' => self::RACE_TYPE_TITLE__NIGHT_MARATHON,
        ];

        if (empty($eventsMap[$matches[1]]) || empty($raceTypeMap[$matches[1] . $matches[4]])) {
            throw new ParseResultsException(sprintf('Unable to find race|event type "%s"', $code));
        }

//        return [
//             'event-type' => $eventsMap[$matches[1]],
//             ResultsTableParser::RACE_TYPE => $raceTypeMap[$matches[1] . $matches[4]],
//             ResultsTableParser::RACE_DISTANCE => $matches[2] ?: self::MARATHON_DISTANCE,
//        ];

        return array_filter($events, fn(Event $event) => $event->name === $eventsMap[$matches[1]])[0];


    }

    protected function getRaceTypeByCode(string $code)
    {


    }

    /**
     * @param Event[] $events
     */
    protected function getEventByRaceCode(array $events)
    {

    }

    #region value formatters

    public static function strToFloat(string $value): ?float
    {
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^\d.]/i', '', $value);

        if (intval($value) === 0) {
            return null;
        }

        return floatval($value);
    }

    public static function strToTimestamp(string $pace): ?int
    {
        if (!preg_match('/(?:(\d?\d):)?(\d?\d):(\d\d)/', $pace, $matches)) {
            return null;
        }

        return strtotime(sprintf('1970-01-01 %d:%d:%d', $matches[1], $matches[2], $matches[3]));
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
        $result->distance = self::strToFloat($resultRow[ResultsTableParser::COL_DISTANCE]);
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

        $result->time = self::strToTimestamp($time);

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