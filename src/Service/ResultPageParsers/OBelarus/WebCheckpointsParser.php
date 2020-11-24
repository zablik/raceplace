<?php

namespace App\Service\ResultPageParsers\OBelarus;

use App\Entity\RaceResultsSource;
use App\Service\ResultPageParsers\Exception\ParseResultsException;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTable;
use App\Service\ResultPageParsers\OBelarus\DTO\CheckpointsTableRow;
use App\Service\ResultPageParsers\WebDataParserInterface;
use App\Service\Utils\NumberUtils;
use App\Service\Utils\TextUtils;
use App\Service\Utils\TimeUtils;
use Symfony\Component\DomCrawler\Crawler;

class WebCheckpointsParser implements WebDataParserInterface
{
    const PROFILE = 'profile';
    const NAME = 'name';
    const NUMBER_PLATE = 'number-plate';
    const RACE_CODE = 'race-code';

    const CHECKPOINTS = 'checkpoints';
    const TOTAL_TIME = 'total-time';
    const PACE = 'pace';
    const DISTANCE = 'distance';
    const TIME = 'time';

    public function parse(string $html, RaceResultsSource $resultsSource): array
    {
        $checkpoints = [];
        $textTables = (new Crawler($html))
            ->filterXPath('//div[@id=\'results-body\']/table/tr')
            ->each(fn(Crawler $node, $i) => $node->children('td pre')->html());

        foreach ($textTables as $textTable) {
            $profileData = $this->parseProfile($textTable);
            $checkpointsData = $this->parseCheckpoints($textTable);

            $table = new CheckpointsTable();
            $table->name = $profileData[WebCheckpointsParser::NAME];
            $table->numberPlate = $profileData[WebCheckpointsParser::NUMBER_PLATE];
            $table->code = $profileData[WebCheckpointsParser::RACE_CODE];

            foreach ($checkpointsData as $checkpoint) {
                $tableRow = new CheckpointsTableRow();
                $tableRow->distance = NumberUtils::strToFloat($checkpoint[WebCheckpointsParser::DISTANCE]);
                $tableRow->pace = TimeUtils::strTimeToDatetime($checkpoint[WebCheckpointsParser::PACE]);
                $tableRow->time = TimeUtils::strTimeToDatetime($checkpoint[WebCheckpointsParser::TIME]);
                $tableRow->totalTime = TimeUtils::strTimeToDatetime($checkpoint[WebCheckpointsParser::TOTAL_TIME]);

                $table->checkpoints[] = $tableRow;
            }

            $checkpoints[] = $table;
        }

        return $checkpoints;
    }

    protected function parseProfile(string $table): array
    {
        $lines = TextUtils::splitLines($table);
        $profileData = [];

        foreach ($lines as $line) {
            if (empty($profileData[self::NAME]) && $name = $this->parseName($line)) {
                $profileData[self::NAME] = $name;
            }
            if (empty($profileData[self::NUMBER_PLATE]) && $numberPlate = $this->parseNumberPlate($line)) {
                $profileData[self::NUMBER_PLATE] = $numberPlate;
            }
            if (empty($profileData[self::RACE_CODE]) && $raceCode = $this->parseRaceCode($line)) {
                $profileData[self::RACE_CODE] = $raceCode;
            }

            $completed = !empty($profileData[self::NAME])
                && !empty($profileData[self::NUMBER_PLATE])
                && !empty($profileData[self::RACE_CODE]);

            if ($completed) {
                break;
            }
        }

        return $profileData;
    }

    protected function parseRaceCode(string $line): ?string
    {
        return $this->parseValue('/^Группа:\s+([\w\d:]+)/ui', $line);
    }

    protected function parseNumberPlate(string $line): ?string
    {
        return $this->parseValue('/^№\s+(\d+),/ui', $line);
    }

    protected function parseName(string $line): ?string
    {
        return $this->parseValue('/^<strong>(.*)<\/strong>/ui', $line);
    }

    protected function parseValue(string $pattern, string $string)
    {
        $value = null;
        if (preg_match($pattern, $string, $matches)) {
            $value = $matches[1];
        }

        return $value;
    }

    protected function getFirstAndLastLines(string $table): array
    {
        $lines = TextUtils::splitLines($table);
        $first = $last = null;

        foreach ($lines as $n => $line) {
            if ($lines[$n] !== '' && str_replace('-', '', $lines[$n]) === '') {
                if (is_null($first)) {
                    $first = $n + 1;
                } elseif (!is_null($first) && is_null($last)) {
                    $last = $n - 1;

                    return [$first, $last];
                }
            }
        }

        throw new ParseResultsException('Can\'t find the first and the last lines to parse checkpoints table!');
    }

    protected function parseCheckpoints(string $table): array
    {
        $lines = TextUtils::splitLines($table);

        list($first, $last) = $this->getFirstAndLastLines($table);
        $checkpoints = [];

        try {
            for ($n = $first; $n <= $last; $n++) {
                $checkpoints[] = $this->parseCheckpoint($lines[$n]);
            }
        } catch (ParseResultsException $e) {
            // TODO: error logging
            return [];
        }

        return $checkpoints;
    }

    protected function parseCheckpoint(string $line): array
    {
        if (!preg_match('/^\s*([\d,]+)\sкм\s+([\d:]+)\s+([\d:]+)\s+([\d:]+)\/км/', $line, $matches)) {
            throw new ParseResultsException(sprintf('Can\'t parse checkpoint row "%s"', $line));
        }

        return [
            self::DISTANCE => $matches[1],
            self::TOTAL_TIME => $matches[2],
            self::TIME => $matches[3],
            self::PACE => $matches[4],
        ];
    }
}
